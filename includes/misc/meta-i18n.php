<?php
/**
 * Translation of free-form course meta values via multilingual plugins.
 *
 * Supported providers (module active only when at least one is detected):
 * - Polylang  – pll_register_string / pll_translate_string (Språk → Oversettelser)
 * - WPML      – wpml_register_single_string (WPML → String Translation)
 * - TranslatePress – trp_translate (visual editor; strings detected when rendered)
 *
 * Not integrated (different model / no string registry):
 * - Weglot, GTranslate, Linguise – translate full HTML output automatically
 * - qTranslate-XT – inline multilingual content, not separate string registry
 * - MultilingualPress – multisite post sync, not meta string translation
 *
 * Without any supported plugin the module is fully inactive (no hooks/overhead).
 *
 * @package Kursagenten
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const KURSAGENTEN_META_STRINGS_OPTION = 'kursagenten_meta_strings';
const KURSAGENTEN_WPML_CONTEXT        = 'kursagenten-meta';
const KURSAGENTEN_META_STRING_MAX_LEN = 300;
const KURSAGENTEN_META_STRING_CAP     = 2000;

/**
 * @return string[]
 */
function kursagenten_meta_i18n_post_types(): array {
    return ['ka_course', 'ka_coursedate'];
}

/**
 * @return array<string, array{group:string, multiline:bool, meta_filter:bool}>
 */
function kursagenten_get_translatable_meta_fields(): array {
    return [
        'ka_course_duration'          => ['group' => 'Kursagenten – Varighet', 'multiline' => false, 'meta_filter' => true],
        'ka_course_time'              => ['group' => 'Kursagenten – Kurstid', 'multiline' => false, 'meta_filter' => true],
        'ka_course_location_freetext' => ['group' => 'Kursagenten – Lokasjon (fritekst)', 'multiline' => true, 'meta_filter' => true],
        'ka_course_location_room'     => ['group' => 'Kursagenten – Rom', 'multiline' => false, 'meta_filter' => true],
        'ka_course_text_before_price' => ['group' => 'Kursagenten – Tekst før pris', 'multiline' => false, 'meta_filter' => true],
        'ka_course_text_after_price'  => ['group' => 'Kursagenten – Tekst etter pris', 'multiline' => false, 'meta_filter' => true],
        'ka_course_difficulty_level'  => ['group' => 'Kursagenten – Vanskelighetsgrad', 'multiline' => false, 'meta_filter' => true],
        'ka_course_type'              => ['group' => 'Kursagenten – Kurstype', 'multiline' => false, 'meta_filter' => true],
        'ka_course_language'          => ['group' => 'Kursagenten – Språk', 'multiline' => false, 'meta_filter' => false],
        'ka_course_button_text'       => ['group' => 'Kursagenten – Knappetekst', 'multiline' => false, 'meta_filter' => false],
    ];
}

/**
 * @return array<string, array{group:string, multiline:bool, meta_filter:bool}>
 */
function kursagenten_get_meta_filter_fields(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = array_filter(
        kursagenten_get_translatable_meta_fields(),
        static fn(array $field): bool => !empty($field['meta_filter'])
    );

    return $cache;
}

function kursagenten_meta_i18n_polylang_active(): bool {
    return function_exists('pll_register_string') || function_exists('pll__');
}

function kursagenten_meta_i18n_wpml_active(): bool {
    return defined('ICL_SITEPRESS_VERSION')
        || has_action('wpml_register_single_string')
        || has_filter('wpml_translate_single_string');
}

function kursagenten_meta_i18n_translatepress_active(): bool {
    return function_exists('trp_translate') || class_exists('TRP_Translate_Press', false);
}

/**
 * Active multilingual providers that support meta string translation.
 *
 * @return string[] slugs: polylang, wpml, translatepress
 */
function kursagenten_meta_i18n_get_providers(): array {
    static $providers = null;
    if ($providers !== null) {
        return $providers;
    }

    $providers = [];
    if (kursagenten_meta_i18n_polylang_active()) {
        $providers[] = 'polylang';
    }
    if (kursagenten_meta_i18n_wpml_active()) {
        $providers[] = 'wpml';
    }
    if (kursagenten_meta_i18n_translatepress_active()) {
        $providers[] = 'translatepress';
    }

    return $providers;
}

function kursagenten_meta_i18n_active(): bool {
    return kursagenten_meta_i18n_get_providers() !== [];
}

/**
 * Stable registration name for Polylang/WPML (unique per meta key + value).
 */
function kursagenten_get_meta_string_registration_name(string $key, string $value): string {
    return $key . '::' . md5($value);
}

/**
 * Whether a meta value should be stored in the translatable registry.
 */
function kursagenten_is_registry_meta_value(string $key, string $value): bool {
    if (!isset(kursagenten_get_translatable_meta_fields()[$key])) {
        return false;
    }

    $value = trim($value);
    if ($value === '' || is_numeric($value)) {
        return false;
    }

    return mb_strlen($value) <= KURSAGENTEN_META_STRING_MAX_LEN;
}

/**
 * Queue a distinct meta value for registration (persisted on shutdown).
 */
function kursagenten_queue_meta_string(string $key, string $value): void {
    if (!kursagenten_meta_i18n_active()) {
        return;
    }
    if (!kursagenten_is_registry_meta_value($key, $value)) {
        return;
    }

    $value = trim($value);

    if (!isset($GLOBALS['kursagenten_meta_string_pending'])) {
        $GLOBALS['kursagenten_meta_string_pending'] = [];
    }
    $GLOBALS['kursagenten_meta_string_pending'][$key][$value] = true;

    if (empty($GLOBALS['kursagenten_meta_string_flush_hooked'])) {
        $GLOBALS['kursagenten_meta_string_flush_hooked'] = true;
        add_action('shutdown', 'kursagenten_flush_meta_strings');
    }
}

/**
 * Collect translatable values from a meta array (e.g. API sync meta_input).
 *
 * @param array<string, mixed> $meta_values
 */
function kursagenten_collect_meta_strings_from_array(array $meta_values): void {
    if (!kursagenten_meta_i18n_active()) {
        return;
    }

    foreach ($meta_values as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }
        kursagenten_queue_meta_string($key, (string) $value);
    }
}

/**
 * Merge queued meta values into the registry option.
 *
 * @return bool True when the registry was updated.
 */
function kursagenten_flush_meta_strings(): bool {
    $pending = $GLOBALS['kursagenten_meta_string_pending'] ?? [];
    if (empty($pending)) {
        return false;
    }

    $registry = get_option(KURSAGENTEN_META_STRINGS_OPTION, []);
    if (!is_array($registry)) {
        $registry = [];
    }

    $changed = false;
    foreach ($pending as $key => $values) {
        if (!is_array($registry[$key] ?? null)) {
            $registry[$key] = [];
        }
        foreach (array_keys($values) as $value) {
            if (count($registry[$key]) >= KURSAGENTEN_META_STRING_CAP) {
                break;
            }
            if (!in_array($value, $registry[$key], true)) {
                $registry[$key][] = $value;
                $changed = true;
            }
        }
    }

    if ($changed) {
        update_option(KURSAGENTEN_META_STRINGS_OPTION, $registry, false);
    }

    $GLOBALS['kursagenten_meta_string_pending'] = [];

    return $changed;
}

/**
 * Scan all existing course/coursedate meta and populate the registry.
 *
 * @return array{keys:int, values:int}
 */
function kursagenten_scan_all_meta_strings(): array {
    if (!kursagenten_meta_i18n_active()) {
        return ['keys' => 0, 'values' => 0];
    }

    global $wpdb;

    $fields   = array_keys(kursagenten_get_translatable_meta_fields());
    $post_types = kursagenten_meta_i18n_post_types();

    if (empty($fields)) {
        return ['keys' => 0, 'values' => 0];
    }

    $placeholders_keys = implode(',', array_fill(0, count($fields), '%s'));
    $placeholders_types = implode(',', array_fill(0, count($post_types), '%s'));

    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
    $sql = $wpdb->prepare(
        "SELECT pm.meta_key, pm.meta_value
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE p.post_type IN ($placeholders_types)
           AND pm.meta_key IN ($placeholders_keys)
           AND pm.meta_value <> ''",
        array_merge($post_types, $fields)
    );

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (!is_array($rows)) {
        return ['keys' => 0, 'values' => 0];
    }

    foreach ($rows as $row) {
        $key   = (string) ($row['meta_key'] ?? '');
        $value = (string) ($row['meta_value'] ?? '');
        kursagenten_queue_meta_string($key, $value);
    }

    kursagenten_flush_meta_strings();

    $registry = get_option(KURSAGENTEN_META_STRINGS_OPTION, []);
    if (!is_array($registry)) {
        return ['keys' => 0, 'values' => 0];
    }

    $value_count = 0;
    foreach ($registry as $values) {
        if (is_array($values)) {
            $value_count += count($values);
        }
    }

    return [
        'keys'   => count($registry),
        'values' => $value_count,
    ];
}

/**
 * Register one meta value with all active multilingual providers.
 */
function kursagenten_register_meta_string_with_plugins(string $key, string $value, string $group, bool $multiline): void {
    $name = kursagenten_get_meta_string_registration_name($key, $value);

    if (kursagenten_meta_i18n_polylang_active() && function_exists('pll_register_string')) {
        pll_register_string($name, $value, $group, $multiline);
    }

    if (kursagenten_meta_i18n_wpml_active()) {
        do_action('wpml_register_single_string', KURSAGENTEN_WPML_CONTEXT, $name, $value);
    }

    // TranslatePress has no register API; strings are picked up when rendered on the
    // frontend or when trp_translate() is called after manual translation in the editor.
}

/**
 * Register all collected meta values with active multilingual providers.
 */
function kursagenten_register_meta_strings(): void {
    if (!kursagenten_meta_i18n_active()) {
        return;
    }

    $registry = get_option(KURSAGENTEN_META_STRINGS_OPTION, []);
    if (!is_array($registry) || empty($registry)) {
        return;
    }

    $fields = kursagenten_get_translatable_meta_fields();

    foreach ($registry as $key => $values) {
        if (!isset($fields[$key]) || !is_array($values)) {
            continue;
        }

        $group     = $fields[$key]['group'];
        $multiline = !empty($fields[$key]['multiline']);

        foreach ($values as $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            kursagenten_register_meta_string_with_plugins($key, $value, $group, $multiline);
        }
    }
}

/**
 * Backfill the registry from the database when empty, then register strings.
 */
function kursagenten_maybe_scan_meta_strings(): void {
    if (!is_admin() || !current_user_can('manage_options') || !kursagenten_meta_i18n_active()) {
        return;
    }

    $registry = get_option(KURSAGENTEN_META_STRINGS_OPTION, []);
    if (is_array($registry) && !empty($registry)) {
        return;
    }

    kursagenten_scan_all_meta_strings();
}

/**
 * Attach meta-i18n hooks only when a supported multilingual plugin is active.
 *
 * Without a supported plugin the entire feature is inert: no hooks, no DB scans,
 * no registry writes, and no overhead on sync or page views.
 */
function kursagenten_meta_i18n_bootstrap(): void {
    if (!kursagenten_meta_i18n_active()) {
        return;
    }

    add_action('added_post_meta', 'kursagenten_collect_meta_string_on_save', 10, 4);
    add_action('updated_post_meta', 'kursagenten_collect_meta_string_on_save', 10, 4);
    add_filter('get_post_metadata', 'kursagenten_filter_translatable_meta', 10, 4);
    add_action('admin_init', 'kursagenten_maybe_scan_meta_strings', 5);
    add_action('admin_init', 'kursagenten_register_meta_strings', 20);
    add_action('init', 'kursagenten_register_meta_strings', 20);
}
add_action('init', 'kursagenten_meta_i18n_bootstrap', 5);

/**
 * Translate via Polylang string translation.
 */
function kursagenten_meta_i18n_translate_polylang(string $value, string $group): string {
    if (!kursagenten_meta_i18n_polylang_active()) {
        return $value;
    }

    if (function_exists('pll_translate_string') && function_exists('pll_current_language')) {
        $lang = pll_current_language('slug');
        if (is_string($lang) && $lang !== '') {
            $translated = pll_translate_string($value, $lang, $group);
            if (is_string($translated) && $translated !== '' && $translated !== $value) {
                return $translated;
            }
        }
    }

    if (function_exists('pll__')) {
        $translated = pll__($value);
        if (is_string($translated) && $translated !== '' && $translated !== $value) {
            return $translated;
        }
    }

    return $value;
}

/**
 * Translate via WPML String Translation.
 */
function kursagenten_meta_i18n_translate_wpml(string $key, string $value): string {
    if (!kursagenten_meta_i18n_wpml_active()) {
        return $value;
    }

    $name       = kursagenten_get_meta_string_registration_name($key, $value);
    $translated = apply_filters('wpml_translate_single_string', $value, KURSAGENTEN_WPML_CONTEXT, $name);
    if (is_string($translated) && $translated !== '' && $translated !== $value) {
        return $translated;
    }

    return $value;
}

/**
 * Translate via TranslatePress (DB / automatic translation).
 */
function kursagenten_meta_i18n_translate_translatepress(string $value): string {
    if (!kursagenten_meta_i18n_translatepress_active() || !function_exists('trp_translate')) {
        return $value;
    }

    $translated = trp_translate($value, null, true);
    if (is_string($translated) && $translated !== '' && $translated !== $value) {
        return $translated;
    }

    return $value;
}

/**
 * Translate a single meta value via Polylang/WPML string translation.
 */
function kursagenten_translate_meta_value(string $key, string $value): string {
    if ($value === '' || !isset(kursagenten_get_translatable_meta_fields()[$key])) {
        return $value;
    }

    if (!kursagenten_meta_i18n_active()) {
        return $value;
    }

    kursagenten_queue_meta_string($key, $value);

    if (kursagenten_is_norwegian_locale()) {
        return $value;
    }

    $fields = kursagenten_get_translatable_meta_fields();
    $group  = $fields[$key]['group'] ?? 'Kursagenten';

    foreach (kursagenten_meta_i18n_get_providers() as $provider) {
        if ($provider === 'polylang') {
            $translated = kursagenten_meta_i18n_translate_polylang($value, $group);
        } elseif ($provider === 'wpml') {
            $translated = kursagenten_meta_i18n_translate_wpml($key, $value);
        } elseif ($provider === 'translatepress') {
            $translated = kursagenten_meta_i18n_translate_translatepress($value);
        } else {
            continue;
        }

        if ($translated !== $value) {
            return $translated;
        }
    }

    $gettext = __($value, 'kursagenten');
    if ($gettext !== $value) {
        return $gettext;
    }

    return $value;
}

/**
 * @param mixed $value
 * @return mixed
 */
function kursagenten_filter_translatable_meta($value, $object_id, $meta_key, $single) {
    static $guard = false;

    if ($guard || !is_string($meta_key)) {
        return $value;
    }
    if (!isset(kursagenten_get_meta_filter_fields()[$meta_key])) {
        return $value;
    }
    if (is_admin() && !(function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return $value;
    }
    if (!kursagenten_meta_i18n_active()) {
        return $value;
    }

    $guard = true;
    $raw   = get_post_meta((int) $object_id, $meta_key, true);
    $guard = false;

    if (!is_string($raw) || $raw === '') {
        return $value;
    }

    kursagenten_queue_meta_string($meta_key, $raw);

    if (kursagenten_is_norwegian_locale()) {
        return $value;
    }

    $translated = kursagenten_translate_meta_value($meta_key, $raw);

    return $single ? $translated : [$translated];
}

/**
 * @param mixed $meta_id
 * @param mixed $object_id
 * @param mixed $meta_key
 * @param mixed $meta_value
 */
function kursagenten_collect_meta_string_on_save($meta_id, $object_id, $meta_key, $meta_value): void {
    if (!is_string($meta_key) || !is_scalar($meta_value)) {
        return;
    }
    if (!in_array(get_post_type((int) $object_id), kursagenten_meta_i18n_post_types(), true)) {
        return;
    }

    kursagenten_queue_meta_string($meta_key, (string) $meta_value);
}
