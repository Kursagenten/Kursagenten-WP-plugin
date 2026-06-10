<?php
/**
 * Frontend i18n helpers for Kursagenten.
 *
 * @package Kursagenten
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('kursagenten_normalize_bool')) {
    /**
     * Normalize truthy values from metadata to strict booleans.
     */
    function kursagenten_normalize_bool($value): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }
}

/**
 * Datepicker (Caleran) labels shared by course-ajax-filter.js and course-list shortcode.
 *
 * @return array<string, mixed>
 */
function kursagenten_get_datepicker_i18n(): array {
    return [
        'applyLabel'  => __('Bruk', 'kursagenten'),
        'cancelLabel' => __('Avbryt', 'kursagenten'),
        'rangeLabel'  => __('Velg periode', 'kursagenten'),
        'ranges'      => [
            [
                'title'     => __('Neste uke', 'kursagenten'),
                'startDate' => null,
                'endDate'   => null,
                'weeks'     => 1,
            ],
            [
                'title'     => __('Neste 3 måneder', 'kursagenten'),
                'startDate' => null,
                'endDate'   => null,
                'months'    => 3,
            ],
            [
                'title'     => __('Neste 6 måneder', 'kursagenten'),
                'startDate' => null,
                'endDate'   => null,
                'months'    => 6,
            ],
            [
                'title'     => __('Resten av året', 'kursagenten'),
                'startDate' => null,
                'endDate'   => null,
                'endOfYear' => true,
            ],
            [
                'title'     => __('Neste år', 'kursagenten'),
                'startDate' => null,
                'endDate'   => null,
                'nextYear'  => true,
            ],
        ],
    ];
}

/**
 * Canonical filter labels/placeholders (translated at runtime for the active locale).
 *
 * Stored option values may contain Norwegian from when settings were saved in admin.
 *
 * @return array<string, array{label: string, placeholder: string}>
 */
function kursagenten_get_available_filter_definitions(): array {
    return [
        'search' => [
            'label'       => __('Søk', 'kursagenten'),
            'placeholder' => __('Søk etter kurs', 'kursagenten'),
        ],
        'categories' => [
            'label'       => __('Kategorier', 'kursagenten'),
            'placeholder' => __('Velg kategori', 'kursagenten'),
        ],
        'locations' => [
            'label'       => __('Kurssteder', 'kursagenten'),
            'placeholder' => __('Velg kurssted', 'kursagenten'),
        ],
        'instructors' => [
            'label'       => __('Instruktører', 'kursagenten'),
            'placeholder' => __('Velg instruktør', 'kursagenten'),
        ],
        'language' => [
            'label'       => __('Språk', 'kursagenten'),
            'placeholder' => __('Velg språk', 'kursagenten'),
        ],
        'time_of_day' => [
            'label'       => __('Dag-/kveldskurs', 'kursagenten'),
            'placeholder' => __('Velg tidspunkt', 'kursagenten'),
        ],
        'price' => [
            'label'       => __('Pris', 'kursagenten'),
            'placeholder' => __('Velg pris', 'kursagenten'),
        ],
        'date' => [
            'label'       => __('Startdato', 'kursagenten'),
            'placeholder' => __('Velg dato', 'kursagenten'),
        ],
        'months' => [
            'label'       => __('Startmåned', 'kursagenten'),
            'placeholder' => __('Velg måned', 'kursagenten'),
        ],
        'availability' => [
            'label'       => __('Ledige kurs', 'kursagenten'),
            'placeholder' => __('Vis kun ledige', 'kursagenten'),
        ],
    ];
}

/**
 * Apply runtime translations to filter metadata loaded from the database.
 *
 * @param array<string, array<string, mixed>> $available_filters Option value from kursagenten_available_filters.
 * @return array<string, array<string, mixed>>
 */
function kursagenten_localize_available_filters(array $available_filters): array {
    $definitions = kursagenten_get_available_filter_definitions();
    $localized   = [];

    foreach ($available_filters as $filter_key => $filter_info) {
        if (!is_array($filter_info)) {
            continue;
        }

        $localized[$filter_key] = $filter_info;

        if (isset($definitions[$filter_key]['label'])) {
            $localized[$filter_key]['label'] = $definitions[$filter_key]['label'];
        }

        if (isset($definitions[$filter_key]['placeholder'])) {
            $localized[$filter_key]['placeholder'] = $definitions[$filter_key]['placeholder'];
        }
    }

    return $localized;
}

/**
 * Build filter display metadata for templates and AJAX.
 *
 * @param array<string, array<string, mixed>> $available_filters Option value from kursagenten_available_filters.
 * @param array<string, array<string, mixed>> $taxonomy_data     Optional taxonomy/url metadata per filter key.
 * @return array<string, array<string, mixed>>
 */
function kursagenten_build_filter_display_info(array $available_filters, array $taxonomy_data = []): array {
    $localized = kursagenten_localize_available_filters($available_filters);
    $display   = [];

    foreach ($localized as $filter_key => $filter_info) {
        $display[$filter_key] = [
            'label'       => $filter_info['label'] ?? '',
            'placeholder' => $filter_info['placeholder'] ?? __('Velg', 'kursagenten'),
            'filter_key'  => $taxonomy_data[$filter_key]['filter_key'] ?? '',
            'url_key'     => $taxonomy_data[$filter_key]['url_key'] ?? '',
        ];
    }

    return $display;
}

/**
 * UI strings passed to frontend JavaScript via wp_localize_script.
 *
 * @return array<string, string>
 */
function kursagenten_get_frontend_js_i18n(): array {
    return [
        'select'                    => __('Velg', 'kursagenten'),
        'availablePlaces'           => __('Ledige plasser', 'kursagenten'),
        'showOnlyAvailable'         => __('Vis kun ledige plasser', 'kursagenten'),
        'showAllCourses'            => __('Vis alle kurs', 'kursagenten'),
        'removeFilter'              => __('Fjern filter', 'kursagenten'),
        'filterErrorUnknown'        => __('En ukjent feil oppstod under filtreringen.', 'kursagenten'),
        'showMore'                  => __('Vis mer', 'kursagenten'),
        'close'                     => __('Lukk', 'kursagenten'),
        'showFewerLocations'        => __('Vis færre', 'kursagenten'),
        'showMoreLocations'         => __('Vis flere lokasjoner', 'kursagenten'),
        'emptyFilterTooltip'        => __('Ingen kurs tilgjengelige med valgte filtre. Nullstill filtre hvis du står fast.', 'kursagenten'),
        'datepickerApply'           => __('Bruk', 'kursagenten'),
        'datepickerCancel'          => __('Avbryt', 'kursagenten'),
        'datepickerRangeLabel'      => __('Velg periode', 'kursagenten'),
        'datepickerNextWeek'        => __('Neste uke', 'kursagenten'),
        'datepickerNext3Months'     => __('Neste 3 måneder', 'kursagenten'),
        'datepickerNext6Months'     => __('Neste 6 måneder', 'kursagenten'),
        'datepickerRestOfYear'      => __('Resten av året', 'kursagenten'),
        'datepickerNextYear'        => __('Neste år', 'kursagenten'),
        'filterTitle'               => __('Filter', 'kursagenten'),
        'closeFilter'               => __('Lukk filter', 'kursagenten'),
        'searchCoursesPlaceholder'  => __('Søk etter kurs...', 'kursagenten'),
        'selectDateRangePlaceholder' => __('Velg fra-til dato', 'kursagenten'),
        'selectDatesAria'           => __('Velg datoer', 'kursagenten'),
        'resetDate'                 => __('Nullstill dato', 'kursagenten'),
        'resetFilters'              => __('Nullstill filter', 'kursagenten'),
        'filterCourses'             => __('Filtrer kurs', 'kursagenten'),
        'showCoursesPerPage'        => __('Vis antall kurs', 'kursagenten'),
        'sortBy'                    => __('Sorter etter', 'kursagenten'),
        'sortStandard'              => __('Standard', 'kursagenten'),
        'sortTitleAsc'              => __('Fra A til Å', 'kursagenten'),
        'sortTitleDesc'             => __('Fra Å til A', 'kursagenten'),
        'sortPriceAsc'              => __('Pris lav til høy', 'kursagenten'),
        'sortPriceDesc'             => __('Pris høy til lav', 'kursagenten'),
        'sortDateAsc'               => __('Tidligste dato', 'kursagenten'),
        'sortDateDesc'              => __('Seneste dato', 'kursagenten'),
        'filterSelected'            => __('%1$d %2$s valgt', 'kursagenten'),
        'noCoursesFound'            => __('Ingen kurs funnet. Fjern ett eller flere filtre, eller nullstill alle filtre.', 'kursagenten'),
        'noFiltersConfigured'       => __('Ingen filtre er konfigurert.', 'kursagenten'),
        'noFilterOptions'           => __('Ingen alternativer tilgjengelig', 'kursagenten'),
        'showResults'               => __('Vis resultater', 'kursagenten'),
        'filterPanelNoResults'      => __('Ingen treff med valgte filtre. Prøv et annet søk eller juster filtrene.', 'kursagenten'),
        'loadFiltersError'          => __('Kunne ikke laste filtrene. Vennligst prøv igjen.', 'kursagenten'),
        'retry'                     => __('Prøv igjen', 'kursagenten'),
        'previous'                  => __('Forrige', 'kursagenten'),
        'next'                      => __('Neste', 'kursagenten'),
    ];
}

/**
 * Format course count label for list header and AJAX responses.
 *
 * @param int $count     Number of courses found.
 * @param int $paged     Current page (0 to omit pagination suffix).
 * @param int $max_pages Total pages (0 to omit pagination suffix).
 */
function kursagenten_format_course_count_label(int $count, int $paged = 0, int $max_pages = 0): string {
    $label = sprintf(
        /* translators: %d: number of courses */
        _n('%d kurs', '%d kurs', $count, 'kursagenten'),
        $count
    );

    if ($max_pages > 1 && $paged > 0) {
        $label .= ' ' . sprintf(
            /* translators: 1: current page number, 2: total number of pages */
            __('– side %1$d av %2$d', 'kursagenten'),
            $paged,
            $max_pages
        );
    }

    return $label;
}

/**
 * Pagination prev/next markup for course lists.
 *
 * @return array{prev_text: string, next_text: string}
 */
function kursagenten_get_course_list_pagination_texts(): array {
    return [
        'prev_text' => '<i class="ka-icon icon-chevron-left"></i> <span>' . esc_html__('Forrige', 'kursagenten') . '</span>',
        'next_text' => '<span>' . esc_html__('Neste', 'kursagenten') . '</span> <i class="ka-icon icon-chevron-right"></i>',
    ];
}

/**
 * Base data for kurskalender_data localize object.
 *
 * @param array<string, mixed> $extra Additional keys to merge (e.g. shortcode_params).
 * @return array<string, mixed>
 */
function kursagenten_get_kurskalender_localize_data(array $extra = []): array {
    return array_merge(
        [
            'ajax_url'               => admin_url('admin-ajax.php'),
            'filter_nonce'           => wp_create_nonce('filter_nonce'),
            'default_available_only' => (get_option('kursagenten_default_available_only', 'no') === 'yes'),
            'i18n'                   => kursagenten_get_frontend_js_i18n(),
            'datepicker'             => kursagenten_get_datepicker_i18n(),
        ],
        $extra
    );
}

/**
 * Localize expand-content script strings.
 */
function kursagenten_localize_expand_content_script(): void {
    if (!wp_script_is('kursagenten-expand-content', 'registered') && !wp_script_is('kursagenten-expand-content', 'enqueued')) {
        return;
    }

    wp_localize_script(
        'kursagenten-expand-content',
        'kursagentenExpandContent',
        [
            'i18n' => [
                'showMore'           => __('Vis mer', 'kursagenten'),
                'close'              => __('Lukk', 'kursagenten'),
                'showFewerLocations' => __('Vis færre', 'kursagenten'),
                'showMoreLocations'  => __('Vis flere lokasjoner', 'kursagenten'),
            ],
        ]
    );
}

add_action('wp_enqueue_scripts', 'kursagenten_localize_expand_content_script', 20);

/**
 * Whether the active locale is Norwegian (source language in code/API).
 */
function kursagenten_is_norwegian_locale(): bool {
    if (function_exists('kursagenten_get_active_translation_locale') && function_exists('kursagenten_is_norwegian_plugin_locale')) {
        return kursagenten_is_norwegian_plugin_locale(kursagenten_get_active_translation_locale());
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    return kursagenten_is_norwegian_plugin_locale((string) $locale);
}

/**
 * Resolve course button label from API meta and registration mode.
 *
 * ka_course_button_text comes from the Kursagenten API (formButtonText) and is
 * typically Norwegian until the API supports multiple languages. Known default
 * phrases are mapped to translated strings on non-Norwegian locales. Custom API
 * text is shown as-is. When empty, fall back from ka_course_showRegistrationForm:
 * signup → "Meld deg på", interest → "Meld interesse".
 *
 * @param string $button_text       Value from ka_course_button_text.
 * @param bool   $show_registration True when registration form is enabled.
 */
function kursagenten_get_course_button_label(string $button_text, bool $show_registration): string {
    $button_text = trim($button_text);

    if ($button_text === '') {
        return $show_registration
            ? __('Meld deg på', 'kursagenten')
            : __('Meld interesse', 'kursagenten');
    }

    if (kursagenten_is_norwegian_locale()) {
        return $button_text;
    }

    $translated = __($button_text, 'kursagenten');
    if ($translated !== $button_text) {
        return $translated;
    }

    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower($button_text, 'UTF-8')
        : strtolower($button_text);

    $known_labels = [
        'meld deg på'     => __('Meld deg på', 'kursagenten'),
        'meld interesse'  => __('Meld interesse', 'kursagenten'),
        'påmelding'       => __('Påmelding', 'kursagenten'),
        'registrer deg'   => __('Registrer deg', 'kursagenten'),
        'book time'       => __('Book time', 'kursagenten'),
        'søk utdannelse'  => __('Søk utdannelse', 'kursagenten'),
        'søk utdanning'   => __('Søk utdanning', 'kursagenten'),
        'kjøp prøvetime'  => __('Kjøp prøvetime', 'kursagenten'),
        'kjøp prøvetimer' => __('Kjøp prøvetimer', 'kursagenten'),
        'kjøp'            => __('Kjøp', 'kursagenten'),
    ];

    if (isset($known_labels[$normalized])) {
        return $known_labels[$normalized];
    }

    // Fall back to Polylang/WPML string translation for custom API button text.
    if (function_exists('kursagenten_translate_meta_value')) {
        return kursagenten_translate_meta_value('ka_course_button_text', $button_text);
    }

    return $button_text;
}

/**
 * Strings for inline taxonomy filter scripts (category/location chips).
 *
 * @return array<string, string>
 */
function kursagenten_get_taxonomy_inline_i18n(): array {
    return [
        /* translators: %d is the number of visible courses */
        'allCount' => __('Alle (%d)', 'kursagenten'),
    ];
}
