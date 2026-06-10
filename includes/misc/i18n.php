<?php
/**
 * Internationalization bootstrap for Kursagenten.
 *
 * @package Kursagenten
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Relative path to lang/ (for load_plugin_textdomain).
 */
function kursagenten_get_lang_dir_rel(): string {
    return dirname(KURSAG_PLUGIN_BASE) . '/lang';
}

/**
 * Absolute path to lang/.
 */
function kursagenten_get_lang_dir_path(): string {
    return KURSAG_PLUGIN_DIR . 'lang/';
}

/**
 * Map Polylang/WPML short locale codes to bundled .mo file names.
 */
function kursagenten_resolve_plugin_locale(string $locale): string {
    $lang_dir = kursagenten_get_lang_dir_path();
    $exact_mo = $lang_dir . 'kursagenten-' . $locale . '.mo';

    if (is_readable($exact_mo)) {
        return $locale;
    }

    $short_map = [
        'en' => 'en_US',
        'de' => 'de_DE',
        'fr' => 'fr_FR',
        'es' => 'es_ES',
        'pl' => 'pl_PL',
    ];

    $primary = strtolower(strtok($locale, '_') ?: $locale);
    if (!isset($short_map[$primary])) {
        return $locale;
    }

    $mapped = $short_map[$primary];
    $mapped_mo = $lang_dir . 'kursagenten-' . $mapped . '.mo';

    return is_readable($mapped_mo) ? $mapped : $locale;
}

/**
 * Whether a locale should use Norwegian source strings from code.
 */
function kursagenten_is_norwegian_plugin_locale(string $locale): bool {
    $locale = strtolower($locale);
    if (in_array($locale, ['nb_no', 'nb', 'nn_no', 'nn', 'no_no', 'no'], true)) {
        return true;
    }

    return str_starts_with($locale, 'nb_') || str_starts_with($locale, 'nn_');
}

/**
 * Polylang locale for the current frontend request, if available.
 */
function kursagenten_get_polylang_locale(): ?string {
    if (!function_exists('pll_current_language')) {
        return null;
    }

    if (is_admin() && !(function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return null;
    }

    if (function_exists('PLL')) {
        $pll = PLL();
        if (is_object($pll) && isset($pll->curlang) && is_object($pll->curlang) && !empty($pll->curlang->locale)) {
            return (string) $pll->curlang->locale;
        }
    }

    $locale = pll_current_language('locale');
    if (is_string($locale) && $locale !== '') {
        return $locale;
    }

    // Content-based language detection may not expose locale until the current post is known.
    if (function_exists('pll_get_post_language')) {
        $post_id = get_queried_object_id();
        if ($post_id > 0) {
            $post_locale = pll_get_post_language($post_id, 'locale');
            if (is_string($post_locale) && $post_locale !== '') {
                return $post_locale;
            }
        }
    }

    $slug = pll_current_language('slug');
    if (is_string($slug) && $slug !== '' && function_exists('PLL')) {
        $pll = PLL();
        if (isset($pll->model) && is_object($pll->model) && method_exists($pll->model, 'get_language')) {
            $language = $pll->model->get_language($slug);
            if (is_object($language) && !empty($language->locale)) {
                return (string) $language->locale;
            }
        }
    }

    return null;
}

/**
 * WPML locale for the current frontend request, if available.
 */
function kursagenten_get_wpml_locale(): ?string {
    if (!defined('ICL_SITEPRESS_VERSION')) {
        return null;
    }

    global $sitepress;
    if (!is_object($sitepress) || !method_exists($sitepress, 'get_current_language')) {
        return null;
    }

    $code = (string) $sitepress->get_current_language();
    if ($code === '' || !method_exists($sitepress, 'get_locale_from_language_code')) {
        return null;
    }

    $locale = (string) $sitepress->get_locale_from_language_code($code);

    return $locale !== '' ? $locale : null;
}

/**
 * Permalink for a post in the active frontend language (Polylang/WPML).
 */
function kursagenten_get_localized_permalink(int $post_id): string {
    if ($post_id <= 0) {
        return '';
    }

    $target_id = $post_id;

    if (function_exists('pll_get_post') && function_exists('pll_current_language')) {
        $lang = pll_current_language('slug');
        if (is_string($lang) && $lang !== '') {
            $translated_id = (int) pll_get_post($post_id, $lang);
            if ($translated_id > 0) {
                $target_id = $translated_id;
            }
        }
    }

    $permalink = get_permalink($target_id);

    return is_string($permalink) ? $permalink : '';
}

/**
 * Locale that should drive Kursagenten UI translations on this request.
 */
function kursagenten_get_active_translation_locale(): string {
    $requested = kursagenten_get_polylang_locale() ?? kursagenten_get_wpml_locale();

    if ($requested !== null) {
        return kursagenten_resolve_plugin_locale($requested);
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    return kursagenten_resolve_plugin_locale((string) $locale);
}

/**
 * Load seed JSON translations for a locale (fallback when .mo is not applied).
 *
 * @return array<string, string>
 */
function kursagenten_get_seed_translation_map(string $locale): array {
    static $cache = [];

    if (isset($cache[$locale])) {
        return $cache[$locale];
    }

    $map      = [];
    $lang_dir = kursagenten_get_lang_dir_path();
    $files    = [
        $lang_dir . 'translations-' . $locale . '.json',
        $lang_dir . 'block-editor-' . $locale . '.json',
    ];

    foreach ($files as $file) {
        if (!is_readable($file)) {
            continue;
        }

        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) {
            $map = array_merge($map, $decoded);
        }
    }

    $cache[$locale] = $map;

    return $map;
}

/**
 * Load .mo translations for the kursagenten text domain.
 */
function kursagenten_load_textdomain_for_locale(string $locale): void {
    $locale = kursagenten_resolve_plugin_locale($locale);
    $mofile = kursagenten_get_lang_dir_path() . 'kursagenten-' . $locale . '.mo';

    unload_textdomain('kursagenten');

    if (!is_readable($mofile)) {
        return;
    }

    load_textdomain('kursagenten', $mofile);
}

/**
 * Load plugin translations from /lang for the active locale.
 */
function kursagenten_load_textdomain(): void {
    kursagenten_load_textdomain_for_locale(kursagenten_get_active_translation_locale());
}

/**
 * Apply Polylang/WPML locale before translating frontend strings.
 *
 * Call at the start of shortcodes/AJAX handlers when in doubt.
 */
function kursagenten_ensure_frontend_translations(): void {
    static $applied_locale = null;

    if (is_admin() && !(function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return;
    }

    $locale = kursagenten_get_active_translation_locale();

    if ($applied_locale === $locale) {
        return;
    }

    if (function_exists('switch_to_locale')) {
        switch_to_locale($locale);
    }

    kursagenten_load_textdomain_for_locale($locale);
    $applied_locale = $locale;
}

/**
 * Use bundled locale file names for this text domain.
 */
function kursagenten_filter_plugin_locale(string $locale, string $domain): string {
    if ($domain !== 'kursagenten') {
        return $locale;
    }

    return kursagenten_resolve_plugin_locale($locale);
}

add_filter('plugin_locale', 'kursagenten_filter_plugin_locale', 10, 2);

/**
 * JSON seed fallback when gettext returns the Norwegian source string.
 */
function kursagenten_gettext_seed_fallback(string $translation, string $text, string $domain): string {
    if ($domain !== 'kursagenten' || $translation !== $text) {
        return $translation;
    }

    $locale = kursagenten_get_active_translation_locale();
    if (kursagenten_is_norwegian_plugin_locale($locale)) {
        return $translation;
    }

    $map = kursagenten_get_seed_translation_map($locale);

    return $map[$text] ?? $translation;
}

add_filter('gettext', 'kursagenten_gettext_seed_fallback', 20, 3);

/**
 * Plural seed fallback for _n() when .mo plurals are not loaded.
 */
function kursagenten_ngettext_seed_fallback(
    string $translation,
    string $single,
    string $plural,
    int $number,
    string $domain
): string {
    if ($domain !== 'kursagenten') {
        return $translation;
    }

    if ($translation !== $single && $translation !== $plural) {
        return $translation;
    }

    $locale = kursagenten_get_active_translation_locale();
    if (kursagenten_is_norwegian_plugin_locale($locale)) {
        return $translation;
    }

    static $plural_maps = [];
    if (!isset($plural_maps[$locale])) {
        $file = kursagenten_get_lang_dir_path() . 'plurals-' . $locale . '.json';
        if (!is_readable($file)) {
            $plural_maps[$locale] = [];
        } else {
            $decoded = json_decode((string) file_get_contents($file), true);
            $plural_maps[$locale] = is_array($decoded) ? $decoded : [];
        }
    }

    $entry = $plural_maps[$locale][$single] ?? null;
    if (!is_array($entry) || empty($entry['forms']) || !is_array($entry['forms'])) {
        return $translation;
    }

    $forms = $entry['forms'];
    $index = ($number === 1) ? 0 : 1;
    if ($locale === 'pl_PL' && count($forms) >= 3) {
        $mod10  = $number % 10;
        $mod100 = $number % 100;
        if ($number === 1) {
            $index = 0;
        } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
            $index = 1;
        } else {
            $index = 2;
        }
    } elseif ($locale === 'fr_FR' && count($forms) >= 2) {
        $index = ($number > 1) ? 1 : 0;
    }

    return (string) ($forms[$index] ?? $forms[0]);
}

add_filter('ngettext', 'kursagenten_ngettext_seed_fallback', 20, 5);

/**
 * Align WordPress locale with Polylang/WPML on the frontend.
 */
function kursagenten_filter_frontend_locale(string $locale): string {
    if (is_admin() && !(function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return $locale;
    }

    $requested = kursagenten_get_polylang_locale() ?? kursagenten_get_wpml_locale();
    if ($requested === null) {
        return $locale;
    }

    return kursagenten_resolve_plugin_locale($requested);
}

add_filter('locale', 'kursagenten_filter_frontend_locale', 100);
add_filter('determine_locale', 'kursagenten_filter_frontend_locale', 100);

// Polylang defines language on wp:5 when detecting from page content.
add_action('pll_language_defined', 'kursagenten_ensure_frontend_translations', 1);
add_action('wp', 'kursagenten_ensure_frontend_translations', 6);

// WPML.
add_action('wpml_language_has_switched', 'kursagenten_ensure_frontend_translations', 0);

// Admin / REST uses user/site locale.
add_action('admin_init', 'kursagenten_load_textdomain', 0);
add_action('change_locale', 'kursagenten_load_textdomain', 0);

/**
 * Apply frontend locale for Kursagenten AJAX handlers (Polylang passes ?lang=).
 */
function kursagenten_ensure_ajax_translations(): void {
    if (!function_exists('wp_doing_ajax') || !wp_doing_ajax()) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_key((string) wp_unslash($_REQUEST['action'])) : '';
    $ka_actions = [
        'filter_courses',
        'get_course_price_range',
        'load_mobile_filters',
        'get_filter_counts',
    ];

    if (!in_array($action, $ka_actions, true)) {
        return;
    }

    kursagenten_ensure_frontend_translations();
}

add_action('admin_init', 'kursagenten_ensure_ajax_translations', 0);
