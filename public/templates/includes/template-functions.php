<?php
/**
 * Template loading and handling functions
 */

if (!defined('ABSPATH')) exit;

// Load SEO functions
require_once(dirname(__FILE__) . '/template-seo-functions.php');

/**
 * Laster inn riktig template basert på kontekst og innstillinger
 * Respekterer temaets egne templates først, bruker pluginens templates som fallback
 *
 * @param string $template Original template path
 * @return string Modified template path
 */
function kursagenten_template_loader($template) {
    // Sjekk om vi er på en kursrelatert side
    if (!is_singular('ka_course') && !is_post_type_archive('ka_course') && 
        !is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        return $template;
    }

    // Fikse queried object for taksonomier
    if (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        // Hent term direkte fra URL
        $requested_slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $current_taxonomy = get_query_var('taxonomy');
        
        // Sjekk om vi har en gyldig slug og taksonomi
        if ($requested_slug && $current_taxonomy) {
            $requested_term = get_term_by('slug', $requested_slug, $current_taxonomy);
            if ($requested_term) {
                // Oppdater global $wp_query
                global $wp_query;
                $wp_query->queried_object = $requested_term;
                $wp_query->queried_object_id = $requested_term->term_id;
            }
        }
    }

    // Optional compatibility mode for page builders/theme conditions.
    // When enabled, WordPress keeps the current resolved template (single.php, taxonomy.php, archive.php, etc).
    // Legacy option still supported for backward compatibility.
    $legacy_design_mode = get_option('kursagenten_design_mode', 'plugin');
    $single_design_mode = get_option('kursagenten_single_design_mode', $legacy_design_mode);
    $taxonomy_design_mode = get_option('kursagenten_taxonomy_design_mode', $legacy_design_mode);
    $use_theme_hierarchy = (bool) get_option('kursagenten_use_theme_template_hierarchy', 0);

    if ($use_theme_hierarchy) {
        return $template;
    }
    if (is_singular('ka_course') && $single_design_mode === 'custom') {
        return $template;
    }
    if (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors']) && $taxonomy_design_mode === 'custom') {
        return $template;
    }
    
    // VIKTIG: Sjekk om temaet har egne templates først
    // Dette lar temaer bruke WordPress standard template hierarchy
    if (is_singular('ka_course')) {
        // Sjekk om temaet har single-ka_course.php (kun spesifikk template, ikke generisk single.php)
        $theme_single = locate_template(['single-ka_course.php']);
        if ($theme_single) {
            // Temaet har egen template - la den bruke den
            return $theme_single;
        }
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            // Sjekk WordPress template hierarchy for taksonomier
            // taxonomy-{taxonomy}-{term}.php, taxonomy-{taxonomy}.php, taxonomy.php
            $theme_taxonomy = locate_template([
                "taxonomy-{$tax_name}-{$current_tax->slug}.php",
                "taxonomy-{$tax_name}.php",
                'taxonomy.php'
            ]);
            if ($theme_taxonomy) {
                // Temaet har egen template - la den bruke den
                return $theme_taxonomy;
            }
        }
    } elseif (is_post_type_archive('ka_course')) {
        // Sjekk om temaet har archive-ka_course.php (kun spesifikk template, ikke generisk archive.php)
        $theme_archive = locate_template(['archive-ka_course.php']);
        if ($theme_archive) {
            // Temaet har egen template - la den bruke den
            return $theme_archive;
        }
    }
    
    // Hvis temaet ikke har egne templates, bruk pluginens templates
    // Bestem kontekst og layout
    $context = '';
    $layout = 'default';
    
    if (is_singular('ka_course')) {
        $context = 'single';
        $layout = get_option('kursagenten_single_layout', 'default');
    } elseif (is_post_type_archive('ka_course')) {
        $context = 'archive';
        $layout = get_option('kursagenten_archive_layout', 'default');
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $context = 'taxonomy';
        
        // Sjekk om vi har spesifikke innstillinger for denne taksonomien
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            $override_default = ($tax_name === 'ka_instructors');
            $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
            
            if ($override_enabled) {
                $layout = get_option("kursagenten_taxonomy_{$tax_name}_layout", '');
                if (empty($layout)) {
                    $layout = get_option('kursagenten_taxonomy_layout', 'default');
                }
            } else {
                $layout = get_option('kursagenten_taxonomy_layout', 'default');
            }
        } else {
            $layout = get_option('kursagenten_taxonomy_layout', 'default');
        }
    }

    // Last inn layout-template
    $layout_template = KURSAG_PLUGIN_DIR . 'public/templates/layouts/' . $layout . '.php';
    if (file_exists($layout_template)) {
        return $layout_template;
    }
    
    // Fallback til standard layout
    return KURSAG_PLUGIN_DIR . 'public/templates/layouts/default.php';
}
add_filter('template_include', 'kursagenten_template_loader', 99);

/**
 * Hjelpefunksjon for temaets templates - henter pluginens innhold med wrapper men uten header/footer
 * Brukes når temaet har egne templates som allerede har header/footer
 * 
 * Eksempel bruk i temaets single-ka_course.php:
 * <?php get_header(); ?>
 * <?php kursagenten_get_content(); ?>
 * <?php get_footer(); ?>
 * 
 * Eller i taxonomy-ka_course_location.php:
 * <?php get_header(); ?>
 * <?php kursagenten_get_content(); ?>
 * <?php get_footer(); ?>
 */
function kursagenten_get_content() {
    // Hent variabler fra rammeverket
    global $query, $top_filters, $left_filters, $filter_types, $available_filters, 
           $has_left_filters, $left_column_class, $is_search_only, $search_class, 
           $taxonomy_data, $filter_display_info;
    ?>
    <div id="ka" class="kursagenten-wrapper ka-default-width">
        <main id="ka-main" class="kursagenten-main" role="main">
            <div class="ka-container">
                <?php
                // Last inn riktig design-template basert på kontekst
                kursagenten_get_design_template();
                ?>
            </div>
        </main>
    </div>
    <?php
}

/**
 * Laster inn riktig design-template basert på kontekst og innstillinger
 * Dette er den interne funksjonen som faktisk laster design-templaten
 */
function kursagenten_get_design_template() {
    $design = 'default';
    $context = '';
    
    // Bestem kontekst og hent riktig design-innstilling
    if (is_singular('ka_course')) {
        $context = 'single';
        $design = get_option('kursagenten_single_design', 'default');
    } elseif (is_post_type_archive('ka_course')) {
        $context = 'archive';
        $design = get_option('kursagenten_archive_design', 'default');
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $context = 'taxonomy';
        
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            $override_default = ($tax_name === 'ka_instructors');
            $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
            
            if ($override_enabled) {
                $design = get_option("kursagenten_taxonomy_{$tax_name}_design", '');
                if (empty($design)) {
                    $design = get_option('kursagenten_taxonomy_design', 'default');
                }
            } else {
                $design = get_option('kursagenten_taxonomy_design', 'default');
            }
        } else {
            $design = get_option('kursagenten_taxonomy_design', 'default');
        }
    }

    // Last inn design-template
    $design_template = KURSAG_PLUGIN_DIR . 'public/templates/designs/' . $context . '/' . $design . '.php';
    if (file_exists($design_template)) {
        include $design_template;
    } else {
        // Fallback til standard design
        include KURSAG_PLUGIN_DIR . 'public/templates/designs/' . $context . '/default.php';
    }
}

/**
 * Laster inn riktig listevisning basert på kontekst og innstillinger
 */
function kursagenten_get_list_template() {
    $list_type = 'standard';
    
    // Bestem kontekst og hent riktig listevisning
    if (is_post_type_archive('ka_course')) {
        $list_type = get_option('kursagenten_archive_list_type', 'standard');
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            $override_default = ($tax_name === 'ka_instructors');
            $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
            
            if ($override_enabled) {
                $list_type = get_option("kursagenten_taxonomy_{$tax_name}_list_type", '');
                if (empty($list_type)) {
                    $list_type = get_option('kursagenten_taxonomy_list_type', 'standard');
                }
            } else {
                $list_type = get_option('kursagenten_taxonomy_list_type', 'standard');
            }
        }
    }

    // Last inn listevisning-template
    $list_template = KURSAG_PLUGIN_DIR . 'public/templates/list-types/' . $list_type . '.php';
    if (file_exists($list_template)) {
        include $list_template;
    } else {
        // Fallback til standard listevisning
        include KURSAG_PLUGIN_DIR . 'public/templates/list-types/standard.php';
    }
}

/**
 * Legg til CSS-klasser til body basert på layout og listevisning
 *
 * @param array $classes Eksisterende body-klasser
 * @return array Modifiserte body-klasser
 */
function kursagenten_add_body_classes($classes) {
    // Layout-klasse
    if (is_singular('ka_course')) {
        $layout = get_option('kursagenten_single_layout', 'default');
        $design = get_option('kursagenten_single_design', 'default');
        $classes[] = 'kursagenten-single-' . $design;
    } elseif (is_post_type_archive('ka_course')) {
        $layout = get_option('kursagenten_archive_layout', 'default');
        $list_type = get_option('kursagenten_archive_list_type', 'standard');
        $design = get_option('kursagenten_archive_design', 'default');
        $classes[] = 'kursagenten-archive-' . $design;
        $classes[] = 'kursagenten-list-' . $list_type;
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            $override_default = ($tax_name === 'ka_instructors');
            $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
            
            if ($override_enabled) {
                $layout = get_option("kursagenten_taxonomy_{$tax_name}_layout", '');
                $list_type = get_option("kursagenten_taxonomy_{$tax_name}_list_type", '');
                $design = get_option("kursagenten_taxonomy_{$tax_name}_design", '');
                
                if (empty($layout)) $layout = get_option('kursagenten_taxonomy_layout', 'default');
                if (empty($list_type)) $list_type = get_option('kursagenten_taxonomy_list_type', 'standard');
                if (empty($design)) $design = get_option('kursagenten_taxonomy_design', 'default');
            } else {
                $layout = get_option('kursagenten_taxonomy_layout', 'default');
                $list_type = get_option('kursagenten_taxonomy_list_type', 'standard');
                $design = get_option('kursagenten_taxonomy_design', 'default');
            }
            
            $classes[] = 'kursagenten-taxonomy-' . $design;
            $classes[] = 'kursagenten-list-' . $list_type;
            $classes[] = 'kursagenten-tax-' . $tax_name;
        }
    }
    
    // Only add kag layout classes on Kursagenten pages (uses coursedesign: template pages, shortcode pages, assigned WordPress pages)
    if (class_exists('Designmaler') && Designmaler::is_kursagenten_page()) {
        if (isset($layout) && $layout === 'full-width') {
            $classes[] = 'kag kursagenten-full-width';
        } else {
            $classes[] = 'kag ka-default-width';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'kursagenten_add_body_classes');

/**
 * Hjelper-funksjon for å hente template-deler
 */
function get_course_template_part($args = []) {
    if (
        isset($args['taxonomy']) &&
        is_string($args['taxonomy']) &&
        in_array($args['taxonomy'], ['ka_coursecategory', 'ka_course_location', 'ka_instructors'], true) &&
        !isset($args['is_taxonomy_page'])
    ) {
        $args['is_taxonomy_page'] = true;
    }

    // Sjekk om vi skal tvinge standard visning (fra kortkode)
    $force_standard_view = isset($args['force_standard_view']) && $args['force_standard_view'] === true;
    
    // Sjekk om list_type er sendt som parameter (fra shortcode)
    // Bruk isset() og sjekk at det ikke er tom string
    if (isset($args['list_type']) && $args['list_type'] !== '' && $args['list_type'] !== null) {
        $style = $args['list_type'];
    } elseif ($force_standard_view) {
        // Tving standard visning uansett kontekst
        $style = get_option('kursagenten_archive_list_type', 'standard');
    } elseif (is_post_type_archive('ka_course')) {
        $style = get_option('kursagenten_archive_list_type', 'standard');
    } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        $current_tax = get_queried_object();
        if ($current_tax && isset($current_tax->taxonomy)) {
            $tax_name = $current_tax->taxonomy;
            $override_default = ($tax_name === 'ka_instructors');
            $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
            
            if ($override_enabled) {
                $style = get_option("kursagenten_taxonomy_{$tax_name}_list_type", '');
                if (empty($style)) {
                    $style = get_option('kursagenten_taxonomy_list_type', 'standard');
                }
            } else {
                $style = get_option('kursagenten_taxonomy_list_type', 'standard');
            }
        } else {
            $style = get_option('kursagenten_taxonomy_list_type', 'standard');
        }
    } else {
        // Fallback til global innstilling
        $style = get_option('kursagenten_archive_list_type', 'standard');
    }
    
    // Bygg filnavn og path
    $template_file = "{$style}.php";
    $template_path = KURSAG_PLUGIN_DIR . "public/templates/list-types/{$template_file}";
    
    // Sjekk om template eksisterer
    if (file_exists($template_path)) {
        // Gjør $args tilgjengelig for template-filen
        extract($args);
        include $template_path;
    } else {
        // Fallback til standard template
        extract($args);
        include KURSAG_PLUGIN_DIR . "public/templates/list-types/standard.php";
    }
}

/**
 * Hjelper-funksjon for å hente taksonomi-metadata
 */
function get_taxonomy_meta($term_id, $taxonomy) {
    $meta = array(
        'rich_description' => get_term_meta($term_id, 'rich_description', true),
        'image' => '',
        'icon' => ''
    );
    
    // Hent bilde basert på taksonomi
    switch ($taxonomy) {
        case 'ka_coursecategory':
            $meta['image'] = get_term_meta($term_id, 'image_coursecategory', true);
            $meta['icon'] = get_term_meta($term_id, 'icon_coursecategory', true);
            break;
        case 'ka_course_location':
            $meta['image'] = get_term_meta($term_id, 'image_course_location', true);
            break;
        case 'ka_instructors':
            $meta['image'] = get_term_meta($term_id, 'instructor_image', true);
            break;
    }
    
    return $meta;
}

/**
 * Henter innstillinger for hero-header (toppfelt med bakgrunn)
 * Brukes av single default og taxonomy hero templates
 *
 * @param string $context 'single' eller 'taxonomy'
 * @return array Keys: bg_mode, overlay, font_color, bg_color, use_image, header_classes
 */
function get_hero_header_settings($context = 'single') {
    $prefix = ($context === 'taxonomy') ? 'kursagenten_taxonomy_hero_header_' : 'kursagenten_single_hero_header_';

    $bg_mode = get_option($prefix . 'bg_mode', 'image_placeholder');
    $overlay = get_option($prefix . 'overlay', 'dark');
    $font_color = get_option($prefix . 'font_color', '');
    $bg_color = get_option($prefix . 'bg_color', '');

    $use_image = ($bg_mode !== 'bgcolor_only');
    $header_classes = ['hero-header'];

    if ($use_image) {
        $header_classes[] = 'hero-overlay-' . $overlay;
    } else {
        $header_classes[] = 'hero-bgcolor-only';
    }
    if ($overlay === 'light') {
        $header_classes[] = 'hero-overlay-light';
    } else {
        $header_classes[] = 'hero-overlay-dark';
    }

    return [
        'bg_mode' => $bg_mode,
        'overlay' => $overlay,
        'font_color' => $font_color,
        'bg_color' => $bg_color,
        'use_image' => $use_image,
        'header_classes' => implode(' ', $header_classes),
    ];
}

/**
 * Default visible list fields per list design.
 *
 * @param string $list_type List type slug.
 * @return string[] Field keys.
 */
function kursagenten_get_default_list_display_fields_for_list_type($list_type = '') {
    $list_type = is_string($list_type) ? sanitize_text_field($list_type) : '';

    switch ($list_type) {
        case 'compact':
            return ['location', 'location_freetext', 'day_schedules'];
        case 'date-and-title':
            return ['last_date', 'day_schedules'];
        case 'simple-cards':
            return ['duration', 'day_schedules'];
        case 'standard':
        case 'grid':
        case 'plain':
            return ['time', 'duration', 'day_schedules', 'price', 'location', 'location_freetext', 'room', 'last_date'];
        default:
            // Legacy fallback for other list types (e.g. date-and-title).
            return ['time', 'duration', 'day_schedules', 'price', 'location', 'location_freetext', 'room', 'instructor'];
    }
}

/**
 * Read enabled list display fields from options.
 *
 * The option is stored as a comma-separated string (e.g. "time,room").
 * If the option has never been saved, list-type-specific defaults are returned.
 *
 * @param string $context_base 'archive' or 'taxonomy'.
 * @param string $list_type    Current list type (optional).
 * @param string $taxonomy     Taxonomy slug for taxonomy context (optional).
 * @return string[] Enabled field keys (including location/location_freetext).
 */
function kursagenten_get_list_display_fields_enabled_list($context_base = 'archive', $list_type = '', $taxonomy = '') {
    $field_keys = ['time', 'duration', 'price', 'location', 'location_freetext', 'room', 'instructor', 'last_date', 'registration_deadline', 'day_schedules'];
    $context_base = ($context_base === 'taxonomy') ? 'taxonomy' : 'archive';
    $list_type = is_string($list_type) ? sanitize_text_field($list_type) : '';
    $taxonomy = is_string($taxonomy) ? sanitize_text_field($taxonomy) : '';
    $valid_taxonomies = ['ka_coursecategory', 'ka_course_location', 'ka_instructors'];
    if (!in_array($taxonomy, $valid_taxonomies, true)) {
        $taxonomy = '';
    }

    if ($list_type === '') {
        $saved_list_type = '';
        if ($context_base === 'taxonomy' && $taxonomy !== '') {
            $override_default = ($taxonomy === 'ka_instructors');
            $override_enabled = (bool) get_option("kursagenten_taxonomy_{$taxonomy}_override", $override_default);
            if ($override_enabled) {
                $saved_list_type = get_option("kursagenten_taxonomy_{$taxonomy}_list_type", '');
            }
        }

        if (!is_string($saved_list_type) || $saved_list_type === '') {
            $list_type_option_key = 'kursagenten_' . $context_base . '_list_type';
            $saved_list_type = get_option($list_type_option_key, 'standard');
        }

        if (is_string($saved_list_type) && $saved_list_type !== '') {
            $list_type = $saved_list_type;
        } else {
            $list_type = 'standard';
        }
    }

    $default_fields = kursagenten_get_default_list_display_fields_for_list_type($list_type);
    $default_fields = array_values(array_intersect($default_fields, $field_keys));

    // Sentinel default makes it possible to distinguish "never saved" from "saved as empty".
    $sentinel = '__ka_unset__';
    $saved = $sentinel;
    $option_keys = [];
    if ($context_base === 'taxonomy' && $taxonomy !== '') {
        $override_default = ($taxonomy === 'ka_instructors');
        $override_enabled = (bool) get_option("kursagenten_taxonomy_{$taxonomy}_override", $override_default);
        if ($override_enabled) {
            $option_keys[] = "kursagenten_taxonomy_{$taxonomy}_list_display_fields";
        }
    }
    $option_keys[] = 'kursagenten_' . $context_base . '_list_display_fields';

    foreach ($option_keys as $option_key) {
        $candidate_saved = get_option($option_key, $sentinel);
        if ($candidate_saved !== $sentinel) {
            $saved = $candidate_saved;
            break;
        }
    }

    if ($saved === $sentinel) {
        return $default_fields;
    }

    if (is_array($saved)) {
        $saved = implode(',', $saved);
    }

    if (!is_string($saved)) {
        return $default_fields;
    }

    $parts = array_filter(array_map('trim', explode(',', $saved)));
    return array_values(array_intersect($parts, $field_keys));
}

/**
 * Whether a list item has any location/room data to show.
 */
function kursagenten_list_has_location_data($location, $location_freetext = '', $location_room = '') {
    return !empty($location) || !empty($location_freetext) || !empty($location_room);
}

/**
 * Get effective WordPress date format with safe fallback.
 *
 * @return string
 */
function kursagenten_get_wp_date_format() {
    $format = get_option('date_format');
    return is_string($format) && $format !== '' ? $format : 'd.m.Y';
}

/**
 * Parse a date string already formatted for display.
 *
 * @param string $formatted_date Display date from ka_format_date().
 * @return DateTime|null
 */
function kursagenten_parse_formatted_display_date($formatted_date) {
    if ($formatted_date === '' || $formatted_date === null) {
        return null;
    }

    $formatted_date = trim((string) $formatted_date);
    $wp_format = kursagenten_get_wp_date_format();

    if ($wp_format) {
        $dt = DateTime::createFromFormat($wp_format, $formatted_date);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    foreach (['d.m.Y', 'j.n.Y', 'Y-m-d'] as $fallback_format) {
        $dt = DateTime::createFromFormat($fallback_format, $formatted_date);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    $timestamp = strtotime($formatted_date);
    if ($timestamp !== false) {
        $dt = new DateTime();
        $dt->setTimestamp($timestamp);
        return $dt;
    }

    return null;
}

/**
 * Date format without year (derived from Settings → General → Date format).
 *
 * @return string PHP date format, e.g. "d.m" for "d.m.Y".
 */
function kursagenten_get_date_format_without_year() {
    $format = kursagenten_get_wp_date_format();
    $short = preg_replace('/[Yy]+/', '', $format);
    $short = preg_replace('/[.,\s\-\/]+$/u', '', $short);
    $short = preg_replace('/^[.,\s\-\/]+/u', '', $short);
    $short = trim($short);

    return $short !== '' ? $short : 'd.m';
}

/**
 * Parse raw course datetime value from post meta.
 *
 * @param string $raw_date Raw date value from ka_course_*_date meta.
 * @return DateTime|null
 */
function kursagenten_parse_raw_course_date($raw_date) {
    if ($raw_date === '' || $raw_date === null) {
        return null;
    }

    $raw_date = trim((string) $raw_date);
    foreach (['Y-m-d H:i:s', 'Y-m-d', DATE_ATOM] as $format) {
        $dt = DateTime::createFromFormat($format, $raw_date);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    try {
        return new DateTime($raw_date);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Format start date with optional end date in the same string.
 * Uses hyphen (-) in date ranges.
 * Same day: "07.08.2026"
 * Same month+year: "12.-15.12.2026"
 * Same year, different month: "12.11-15.12.2026"
 *
 * @param string $first_course_date Formatted first date.
 * @param string $last_course_date  Formatted last date.
 * @param bool   $show_last         Whether last date may be appended.
 * @param string $first_course_date_raw Raw first date from post meta (optional).
 * @param string $last_course_date_raw  Raw last date from post meta (optional).
 * @return string Empty when no first date.
 */
function kursagenten_list_format_course_dates($first_course_date, $last_course_date, $show_last, $first_course_date_raw = '', $last_course_date_raw = '') {
    if ($first_course_date === '' || $first_course_date === null) {
        return '';
    }

    $first = (string) $first_course_date;

    if (!$show_last || $last_course_date === '' || $last_course_date === null) {
        return $first;
    }

    $last = (string) $last_course_date;
    $full_format = kursagenten_get_wp_date_format();
    $without_year_format = kursagenten_get_date_format_without_year();
    $dash = '-';

    $first_dt = kursagenten_parse_raw_course_date($first_course_date_raw);
    $last_dt = kursagenten_parse_raw_course_date($last_course_date_raw);
    if (!$first_dt || !$last_dt) {
        // Fallback for legacy callers that only pass formatted date strings.
        $first_dt = kursagenten_parse_formatted_display_date($first);
        $last_dt = kursagenten_parse_formatted_display_date($last);
    }

    if ($first_dt && $last_dt) {
        $same_year = $first_dt->format('Y') === $last_dt->format('Y');
        if ($same_year && $first_dt->format('Y-m-d') === $last_dt->format('Y-m-d')) {
            return wp_date($full_format, $first_dt->getTimestamp());
        }

        if ($same_year) {
            if ($first_dt->format('m') === $last_dt->format('m')) {
                return wp_date('j.', $first_dt->getTimestamp())
                    . $dash
                    . wp_date($full_format, $last_dt->getTimestamp());
            }

            return wp_date($without_year_format, $first_dt->getTimestamp())
                . $dash
                . wp_date($full_format, $last_dt->getTimestamp());
        }

        return $first . ' ' . $dash . ' ' . $last;
    }

    return $first . $dash . $last;
}

/**
 * Map shortcode "vis" token to canonical list display field key.
 *
 * @param string $token Raw token from shortcode attribute.
 * @return string Empty string when token is unknown.
 */
function kursagenten_map_shortcode_visibility_field_key($token) {
    $token = strtolower(trim((string) $token));
    $token = str_replace([' ', '_'], '-', $token);
    $token = trim($token, '-');

    if ($token === '') {
        return '';
    }

    $aliases = [
        'time' => 'time',
        'tid' => 'time',
        'duration' => 'duration',
        'varighet' => 'duration',
        'price' => 'price',
        'pris' => 'price',
        'location' => 'location',
        'sted' => 'location',
        'location-freetext' => 'location_freetext',
        'locationfreetext' => 'location_freetext',
        'fritext-sted' => 'location_freetext',
        'fritekst-sted' => 'location_freetext',
        'fritextsted' => 'location_freetext',
        'fritekststed' => 'location_freetext',
        'room' => 'room',
        'rom' => 'room',
        'lokale' => 'room',
        'rom-lokale' => 'room',
        'instructor' => 'instructor',
        'instruktør' => 'instructor',
        'instruktor' => 'instructor',
        'last-date' => 'last_date',
        'lastdate' => 'last_date',
        'end-date' => 'last_date',
        'enddate' => 'last_date',
        'sluttdato' => 'last_date',
        'registration-deadline' => 'registration_deadline',
        'registrationdeadline' => 'registration_deadline',
        'pameldingsfrist' => 'registration_deadline',
        'påmeldingsfrist' => 'registration_deadline',
    ];

    return $aliases[$token] ?? '';
}

/**
 * Apply shortcode visibility overrides to resolved display fields.
 *
 * Rules:
 * - Plain list (e.g. "tid,pris"): show only listed fields.
 * - Prefixed list (e.g. "-sluttdato,+instruktør"): patch current settings.
 *
 * @param array<string,bool> $result Base visibility map.
 * @param string             $shortcode_vis Raw shortcode "vis" value.
 * @param string[]           $field_keys Allowed field keys.
 * @return array<string,bool>
 */
function kursagenten_apply_shortcode_visibility_overrides(array $result, $shortcode_vis, array $field_keys) {
    $shortcode_vis = trim((string) $shortcode_vis);
    if ($shortcode_vis === '') {
        return $result;
    }

    $tokens = preg_split('/[\s,;|]+/u', $shortcode_vis);
    if (!is_array($tokens) || empty($tokens)) {
        return $result;
    }

    $show_keys = [];
    $hide_keys = [];
    $plain_keys = [];

    foreach ($tokens as $token) {
        $token = trim((string) $token);
        if ($token === '') {
            continue;
        }

        $mode = 'plain';
        $key_token = $token;

        if (strpos($key_token, '+') === 0) {
            $mode = 'show';
            $key_token = ltrim($key_token, '+');
        } elseif (strpos($key_token, '-') === 0 || strpos($key_token, '!') === 0) {
            $mode = 'hide';
            $key_token = ltrim($key_token, '-!');
        } elseif (stripos($key_token, 'ikke-') === 0) {
            $mode = 'hide';
            $key_token = substr($key_token, strlen('ikke-'));
        } elseif (stripos($key_token, 'not-') === 0) {
            $mode = 'hide';
            $key_token = substr($key_token, strlen('not-'));
        }

        if (preg_match('/^([^:=]+)\s*[:=]\s*(.+)$/u', $key_token, $matches)) {
            $key_token = trim((string) $matches[1]);
            $raw_state = strtolower(trim((string) $matches[2]));
            if (in_array($raw_state, ['0', 'false', 'no', 'nei', 'off', 'hide', 'skjul'], true)) {
                $mode = 'hide';
            } elseif (in_array($raw_state, ['1', 'true', 'yes', 'ja', 'on', 'show', 'vis'], true)) {
                $mode = 'show';
            }
        }

        $mapped_key = kursagenten_map_shortcode_visibility_field_key($key_token);
        if ($mapped_key === '' || !in_array($mapped_key, $field_keys, true)) {
            continue;
        }

        if ($mode === 'hide') {
            $hide_keys[$mapped_key] = true;
        } elseif ($mode === 'show') {
            $show_keys[$mapped_key] = true;
        } else {
            $plain_keys[$mapped_key] = true;
        }
    }

    if (!empty($show_keys) || !empty($hide_keys)) {
        foreach (array_keys($plain_keys) as $field_key) {
            $show_keys[$field_key] = true;
        }
        foreach (array_keys($show_keys) as $field_key) {
            $result[$field_key] = true;
        }
        foreach (array_keys($hide_keys) as $field_key) {
            $result[$field_key] = false;
        }

        return $result;
    }

    if (!empty($plain_keys)) {
        foreach ($field_keys as $field_key) {
            $result[$field_key] = false;
        }
        foreach (array_keys($plain_keys) as $field_key) {
            $result[$field_key] = true;
        }
    }

    return $result;
}

/**
 * Resolve which optional list item fields should be visible.
 *
 * @param array $args Arguments passed to list-type templates.
 * @return array<string, bool> Keys: time, duration, price, location, location_freetext, room, instructor, last_date, registration_deadline.
 */
function kursagenten_get_list_display_fields($args = []) {
    $field_keys = ['time', 'duration', 'price', 'location', 'location_freetext', 'room', 'instructor', 'last_date', 'registration_deadline', 'day_schedules'];

    $is_taxonomy_flag = !empty($args['is_taxonomy_page']);
    $resolved_taxonomy = '';
    if (!empty($args['taxonomy']) && is_string($args['taxonomy'])) {
        $resolved_taxonomy = sanitize_text_field($args['taxonomy']);
    } elseif ($is_taxonomy_flag) {
        $queried_object = get_queried_object();
        if (is_object($queried_object) && isset($queried_object->taxonomy) && is_string($queried_object->taxonomy)) {
            $resolved_taxonomy = $queried_object->taxonomy;
        }
    }

    $is_taxonomy_context = $is_taxonomy_flag
        || in_array($resolved_taxonomy, ['ka_coursecategory', 'ka_course_location', 'ka_instructors'], true);
    $context_base = $is_taxonomy_context ? 'taxonomy' : 'archive';
    $resolved_list_type = '';
    if (!empty($args['list_type']) && is_string($args['list_type'])) {
        $resolved_list_type = sanitize_text_field($args['list_type']);
    }
    $enabled = kursagenten_get_list_display_fields_enabled_list($context_base, $resolved_list_type, $resolved_taxonomy);
    $shortcode_vis = '';
    if (!empty($args['shortcode_vis']) && is_string($args['shortcode_vis'])) {
        $shortcode_vis = sanitize_text_field($args['shortcode_vis']);
    }

    $result = [];
    foreach ($field_keys as $field_key) {
        $result[$field_key] = in_array($field_key, $enabled, true);
    }

    if ($shortcode_vis !== '') {
        $result = kursagenten_apply_shortcode_visibility_overrides($result, $shortcode_vis, $field_keys);
    }

    // Sted skal alltid vises i alle listedesign unntatt Enkle kort.
    if ($resolved_list_type !== 'simple-cards') {
        $result['location'] = true;
    }

    return $result;
}

/**
 * Render a clickable "X dager" link that opens the day-schedules popup.
 *
 * The popup itself is rendered client-side by course-day-schedules.js – it lives
 * outside the list item DOM so we can reuse a single modal for all triggers.
 *
 * Returns an empty string when there are fewer than 2 day schedules. A single day
 * is intentionally hidden because it duplicates the existing coursedate (same
 * date, same time, same location) and adds no new information for the user.
 *
 * @param int    $coursedate_id WP post ID of the ka_coursedate to query.
 * @param int    $count         Pre-fetched count from ka_course_day_schedules_count.
 *                              When 0 or less, the function returns an empty string.
 * @param string $course_title  Optional title used as a fallback for the modal header.
 * @param array  $args          Optional overrides:
 *                                - 'icon'    string  Icon CSS class (default 'icon-calendar', '' to omit)
 *                                - 'class'   string  Extra CSS class on the trigger element
 *                                - 'wrapper' string  Wrap output in '<div class="...">...</div>' if set
 *                                - 'tag'     string  Trigger tag: 'a' (default) or 'button'.
 *                                                    Use 'button' when nested inside another <a>.
 * @return string Rendered HTML or empty string.
 */
function kursagenten_render_day_schedules_link($coursedate_id, $count, $course_title = '', $args = []) {
    $coursedate_id = (int) $coursedate_id;
    $count = (int) $count;

    // Threshold is centralized here. A single day schedule equals the coursedate
    // itself, so we only show the popup link when there are >= 2 days to display.
    if ($coursedate_id <= 0 || $count < 2) {
        return '';
    }

    $defaults = [
        'icon'    => 'icon-calendar',
        'class'   => '',
        'wrapper' => '',
        'tag'     => 'a',
    ];
    $args = array_merge($defaults, is_array($args) ? $args : []);
    $tag = in_array($args['tag'], ['a', 'button'], true) ? $args['tag'] : 'a';

    $label = sprintf(
        // translators: %d is the number of course days.
        _n('%d dag', '%d dager', $count, 'kursagenten'),
        $count
    );

    $classes = trim('show-ka-day-schedules ka-day-schedules-link ' . (string) $args['class']);
    $icon_html = $args['icon'] !== ''
        ? '<i class="ka-icon ' . esc_attr($args['icon']) . '" aria-hidden="true"></i> '
        : '';

    if ($tag === 'button') {
        // Use type="button" so the trigger never submits a parent form, and add an
        // inline reset so the link still looks like a link rather than a button.
        $link = sprintf(
            '<button type="button" class="%1$s" data-coursedate-id="%2$d" data-course-title="%3$s">%4$s%5$s</button>',
            esc_attr($classes),
            $coursedate_id,
            esc_attr($course_title),
            $icon_html,
            esc_html($label)
        );
    } else {
        $link = sprintf(
            '<a href="#" class="%1$s" data-coursedate-id="%2$d" data-course-title="%3$s" role="button">%4$s%5$s</a>',
            esc_attr($classes),
            $coursedate_id,
            esc_attr($course_title),
            $icon_html,
            esc_html($label)
        );
    }

    if (!empty($args['wrapper'])) {
        $link = '<div class="' . esc_attr($args['wrapper']) . '">' . $link . '</div>';
    }

    return $link;
}

/**
 * Build HTML links for instructors assigned to a course or coursedate post.
 *
 * @param int $post_id Post ID (ka_course or ka_coursedate).
 * @return string[] List of sanitized HTML anchor elements.
 */
function kursagenten_get_course_instructor_links($post_id) {
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return [];
    }

    $instructors = get_the_terms($post_id, 'ka_instructors');
    if (empty($instructors) || is_wp_error($instructors)) {
        return [];
    }

    return array_map(function ($term) {
        $instructor_url = get_instructor_display_url($term, 'ka_instructors');
        $display_name = function_exists('get_instructor_display_name')
            ? get_instructor_display_name($term)
            : $term->name;

        return '<a href="' . esc_url($instructor_url) . '"><span class="notranslate" translate="no">'
            . esc_html($display_name) . '</span></a>';
    }, $instructors);
}

/**
 * Henter layout-innstilling og returnerer riktig CSS-klasse
 * 
 * @param string $context Kontekst (single, archive, taxonomy)
 * @return string CSS-klasse for layout
 */
function kursagenten_get_layout_class($context = '') {
    $layout_class = 'ka-standard-layout';
    
    if (empty($context)) {
        // Bestem kontekst automatisk
        if (is_singular('ka_course')) {
            $context = 'single';
        } elseif (is_post_type_archive('ka_course')) {
            $context = 'archive';
        } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
            $context = 'taxonomy';
        }
    }
    
    // Hent layout-innstilling basert på kontekst
    switch ($context) {
        case 'single':
            $layout = get_option('kursagenten_single_layout', 'default');
            break;
        case 'archive':
            $layout = get_option('kursagenten_archive_layout', 'default');
            break;
        case 'taxonomy':
            $current_tax = get_queried_object();
            if ($current_tax && isset($current_tax->taxonomy)) {
                $tax_name = $current_tax->taxonomy;
                $override_default = ($tax_name === 'ka_instructors');
                $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
                
                if ($override_enabled) {
                    $layout = get_option("kursagenten_taxonomy_{$tax_name}_layout", '');
                    if (empty($layout)) {
                        $layout = get_option('kursagenten_taxonomy_layout', 'default');
                    }
                } else {
                    $layout = get_option('kursagenten_taxonomy_layout', 'default');
                }
            } else {
                $layout = get_option('kursagenten_taxonomy_layout', 'default');
            }
            break;
        default:
            $layout = 'default';
    }
    
    // Returner riktig CSS-klasse basert på layout
    if ($layout === 'full-width') {
        $layout_class = 'ka-full-width-layout';
    } else {
        $layout_class = 'ka-default-width';
    }
    
    return $layout_class;
}

/**
 * Sjekker om sidebar skal vises basert på innstillinger
 * 
 * @param string $context Kontekst (single, archive, taxonomy)
 * @return bool True hvis sidebar skal vises
 */
function kursagenten_show_sidebar($context = '') {
    // Bestem kontekst automatisk hvis ikke angitt
    if (empty($context)) {
        if (is_singular('ka_course')) {
            $context = 'single';
        } elseif (is_post_type_archive('ka_course')) {
            $context = 'archive';
        } elseif (is_tax(['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
            $context = 'taxonomy';
        }
    }
    
    // Hent sidebar-innstilling basert på kontekst
    switch ($context) {
        case 'single':
            $show_sidebar = get_option('kursagenten_single_sidebar', true);
            break;
        case 'archive':
            $show_sidebar = get_option('kursagenten_archive_sidebar', true);
            break;
        case 'taxonomy':
            $current_tax = get_queried_object();
            if ($current_tax && isset($current_tax->taxonomy)) {
                $tax_name = $current_tax->taxonomy;
                $override_default = ($tax_name === 'ka_instructors');
                $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
                
                if ($override_enabled) {
                    $show_sidebar = get_option("kursagenten_taxonomy_{$tax_name}_sidebar", '');
                    if ($show_sidebar === '') {
                        $show_sidebar = get_option('kursagenten_taxonomy_sidebar', true);
                    }
                } else {
                    $show_sidebar = get_option('kursagenten_taxonomy_sidebar', true);
                }
            } else {
                $show_sidebar = get_option('kursagenten_taxonomy_sidebar', true);
            }
            break;
        default:
            $show_sidebar = true;
    }
    
    return $show_sidebar;
}

/**
 * Henter riktig template for AJAX-forespørsler
 * 
 * @param string $context Kontekst for forespørselen
 * @return string Template path
 */
function get_ajax_template_path($context = 'archive') {
    $style = '';
    
    switch ($context) {
        case 'archive':
            $style = get_option('kursagenten_archive_list_type', 'standard');
            break;
        case 'taxonomy':
            $current_tax = get_queried_object();
            if ($current_tax && isset($current_tax->taxonomy)) {
                $tax_name = $current_tax->taxonomy;
                $override_default = ($tax_name === 'ka_instructors');
                $override_enabled = get_option("kursagenten_taxonomy_{$tax_name}_override", $override_default);
                
                if ($override_enabled) {
                    $style = get_option("kursagenten_taxonomy_{$tax_name}_list_type", '');
                    if (empty($style)) {
                        $style = get_option('kursagenten_taxonomy_list_type', 'standard');
                    }
                } else {
                    $style = get_option('kursagenten_taxonomy_list_type', 'standard');
                }
            }
            break;
        default:
            $style = 'standard';
    }
    
    $template_path = KURSAG_PLUGIN_DIR . "public/templates/list-types/{$style}.php";
    return file_exists($template_path) ? $template_path : KURSAG_PLUGIN_DIR . "public/templates/list-types/standard.php";
}