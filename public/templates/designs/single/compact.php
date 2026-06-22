<?php
/**
 * Single course design: Kompakt – infostripe
 *
 * @package kursagenten
 */

if (!defined('ABSPATH')) {
    exit;
}

// Admin view settings
$admin_view_class = '';
$admin_view       = 'false';

if (current_user_can('editor') || current_user_can('administrator')) {
    $admin_view_class = ' admin-view';
    $admin_view       = 'true';
}

// Get post meta data
$course_id      = get_post_meta(get_the_ID(), 'ka_location_id', true);
$content        = htmlspecialchars_decode((string) get_post_meta(get_the_ID(), 'ka_course_content', true));
$price_posttext = get_post_meta(get_the_ID(), 'ka_course_text_after_price', true);
$button_text    = get_post_meta(get_the_ID(), 'ka_button_text', true);
$main_course_id = get_post_meta(get_the_ID(), 'ka_main_course_id', true);

$is_parent_course    = get_post_meta(get_the_ID(), 'ka_is_parent_course', true);
$main_course_title   = get_post_meta(get_the_ID(), 'ka_main_course_title', true);
$sub_course_location = get_post_meta(get_the_ID(), 'ka_sub_course_location', true);
$contact_name        = get_post_meta(get_the_ID(), 'ka_course_contactperson_name', true);
$contact_phone       = get_post_meta(get_the_ID(), 'ka_course_contactperson_phone', true);
$contact_email       = get_post_meta(get_the_ID(), 'ka_course_contactperson_email', true);
$show_contact_person = (bool) get_option('kursagenten_single_show_contact_person', true);
$show_instructors    = (bool) get_option('kursagenten_single_show_instructors', false);
$wp_content          = get_the_content();
$has_featured_image  = has_post_thumbnail();

// Related coursedates
if ($is_parent_course === 'yes') {
    $related_coursedate = get_posts([
        'post_type'      => 'ka_coursedate',
        'posts_per_page' => -1,
        'meta_query'     => [
            ['key' => 'ka_main_course_id', 'value' => $course_id],
        ],
        'fields'         => 'ids',
    ]);
} else {
    $course_location_terms = wp_get_post_terms(get_the_ID(), 'ka_course_location');

    if (!empty($course_location_terms) && !is_wp_error($course_location_terms)) {
        $location_names = array_map(static function ($term) {
            return $term->name;
        }, $course_location_terms);

        if (!empty($main_course_id)) {
            $meta_query_main = [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    ['key' => 'ka_course_location', 'value' => $location_names, 'compare' => 'IN'],
                    ['key' => 'ka_course_location_freetext', 'value' => $location_names, 'compare' => 'IN'],
                ],
                ['key' => 'ka_main_course_id', 'value' => $main_course_id, 'compare' => '='],
            ];

            $related_coursedate = get_posts([
                'post_type'      => 'ka_coursedate',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query_main,
                'fields'         => 'ids',
            ]);
        } else {
            $course_title = get_post_meta(get_the_ID(), 'ka_course_title', true);

            if (!empty($course_title)) {
                $meta_query_title = [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        ['key' => 'ka_course_location', 'value' => $location_names, 'compare' => 'IN'],
                        ['key' => 'ka_course_location_freetext', 'value' => $location_names, 'compare' => 'IN'],
                    ],
                    ['key' => 'ka_course_title', 'value' => $course_title, 'compare' => '='],
                ];

                $related_coursedate = get_posts([
                    'post_type'      => 'ka_coursedate',
                    'posts_per_page' => -1,
                    'meta_query'     => $meta_query_title,
                    'fields'         => 'ids',
                ]);
            } else {
                $related_coursedate = get_posts([
                    'post_type'      => 'ka_coursedate',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        'relation' => 'OR',
                        ['key' => 'ka_course_location', 'value' => $location_names, 'compare' => 'IN'],
                        ['key' => 'ka_course_location_freetext', 'value' => $location_names, 'compare' => 'IN'],
                    ],
                    'fields'         => 'ids',
                ]);
            }
        }
    } else {
        $related_coursedate = get_posts([
            'post_type'      => 'ka_coursedate',
            'posts_per_page' => -1,
            'meta_query'     => [
                ['key' => 'ka_main_course_id', 'value' => $course_id],
            ],
            'fields'         => 'ids',
        ]);
    }
}

if (empty($related_coursedate) || !is_array($related_coursedate)) {
    $related_coursedate = [];
}

// Categories for header links
$excluded_terms     = ['skjult', 'skjul', 'usynlig', 'inaktiv', 'ikke-aktiv'];
$coursecategories   = wp_get_post_terms(get_the_ID(), 'ka_coursecategory', [
    'exclude' => array_map(static function ($term_slug) {
        $term = get_term_by('slug', $term_slug, 'ka_coursecategory');
        return $term ? $term->term_id : null;
    }, $excluded_terms),
]);
$coursecategory_links = [];
if (!empty($coursecategories) && !is_wp_error($coursecategories)) {
    $coursecategory_links = array_map(static function ($term) {
        return '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
    }, $coursecategories);
}

$selected_coursedate_data = get_selected_coursedate_data($related_coursedate);
$all_coursedates          = get_all_sorted_coursedates($related_coursedate);

$single_display_fields = function_exists('kursagenten_get_single_display_fields_enabled_list')
    ? kursagenten_get_single_display_fields_enabled_list()
    : ['first_date', 'last_date', 'day_schedules', 'time', 'duration', 'language', 'price', 'room'];

$course_locations         = function_exists('get_course_locations') ? get_course_locations(get_the_ID()) : [];
$has_multiple_locations   = count($course_locations) > 1;
$has_multiple_dates       = count($all_coursedates) > 1;
$show_courselist_toggle   = $has_multiple_dates || $has_multiple_locations;
$show_contact_in_infobar  = $show_contact_person && (!empty($contact_name) || !empty($contact_phone) || !empty($contact_email));

$display_location = $sub_course_location;
if (empty($display_location) && !empty($selected_coursedate_data['location'])) {
    $display_location = $selected_coursedate_data['location'];
}

$display_location_url = '';
if (!empty($display_location)) {
    $location_term = get_term_by('name', $display_location, 'ka_course_location');
    if (!$location_term || is_wp_error($location_term)) {
        $location_term = get_term_by('slug', sanitize_title($display_location), 'ka_course_location');
    }
    if ($location_term && !is_wp_error($location_term)) {
        $term_link = get_term_link($location_term);
        if (!is_wp_error($term_link)) {
            $display_location_url = $term_link;
        }
    }

    if ($display_location_url === '') {
        $parent_main_course_id = get_post_meta(get_the_ID(), 'ka_main_course_id', true);
        if (!empty($parent_main_course_id)) {
            $location_child = get_posts([
                'post_type'      => 'ka_course',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'key'     => 'ka_main_course_id',
                        'value'   => $parent_main_course_id,
                        'compare' => '=',
                    ],
                    [
                        'key'     => 'ka_sub_course_location',
                        'value'   => $display_location,
                        'compare' => '=',
                    ],
                ],
            ]);
            if (!empty($location_child)) {
                $display_location_url = get_permalink($location_child[0]->ID);
            }
        }
    }
}

/**
 * Render course featured image.
 *
 * Render course featured image (single instance, positioned via CSS grid).
 */
$render_course_image = static function () use ($has_featured_image) {
    if (!$has_featured_image) {
        return;
    }

    $thumbnail_id = get_post_thumbnail_id(get_the_ID());
    $image_data   = wp_get_attachment_image_src($thumbnail_id, 'large');
    if (empty($image_data[0])) {
        return;
    }
    ?>
    <picture class="compact-course-image compact-grid-image">
        <img src="<?php echo esc_url($image_data[0]); ?>"
             width="<?php echo esc_attr((string) $image_data[1]); ?>"
             height="<?php echo esc_attr((string) $image_data[2]); ?>"
             alt="<?php echo esc_attr(sprintf(__('Bilde for kurs i %s', 'kursagenten'), get_the_title())); ?>"
             title="<?php the_title_attribute(); ?>"
             decoding="async">
    </picture>
    <?php
};

/**
 * Render courselist accordion (dates at current / all locations).
 *
 * @param array $args {
 *     @type bool $panel_mode   Render inside the infostripe toggle panel.
 *     @type bool $expand_first Mark first item for auto-expand when panel opens.
 * }
 */
$render_courselist = static function (array $args = []) use (
    $all_coursedates,
    $price_posttext,
    $show_instructors,
    $single_display_fields
) {
    if (empty($all_coursedates)) {
        return;
    }

    $panel_mode   = !empty($args['panel_mode']);
    $expand_first = !empty($args['expand_first']);
    ?>
    <div class="courselist compact-courselist<?php echo $panel_mode ? ' compact-courselist--panel' : ''; ?>">
        <div class="all-coursedates">
            <p class="compact-courselist-locations"><?php echo display_course_locations(get_the_ID(), array(
                'open_panel_query' => $panel_mode,
            )); ?></p>
            <div class="accordion courselist-items-wrapper expand-content" data-size="<?php echo $panel_mode ? 'auto' : '220px'; ?>">
                <?php
                $total_courses = count($all_coursedates);
                $course_index  = 0;
                foreach ($all_coursedates as $coursedate) :
                    $item_class = $total_courses === 1 ? 'courselist-item single-item' : 'courselist-item';
                    if ($panel_mode && $expand_first && $course_index === 0) {
                        $item_class .= ' compact-courselist-expand-first';
                    }
                    $course_index++;

                    if (isset($coursedate['course_isFull']) && $coursedate['course_isFull'] === true) {
                        $item_class      .= ' ka-full';
                        $available_text   = __('Kurset er fullt', 'kursagenten');
                        $available_class  = 'ka-full';
                    } else {
                        $show_registration = get_post_meta($coursedate['id'], 'ka_course_showRegistrationForm', true);
                        if (empty($show_registration) || $show_registration === 'false') {
                            $item_class     .= ' ka-on-demand';
                            $available_text  = __('På forespørsel', 'kursagenten');
                            $available_class = 'ka-on-demand';
                        } else {
                            $item_class     .= ' ka-available';
                            $available_text  = __('Ledige plasser', 'kursagenten');
                            $available_class = 'ka-available';
                        }
                    }
                    ?>
                    <div class="<?php echo esc_attr($item_class); ?>">
                        <div class="courselist-main ka-cursor-tooltip" data-title="<?php echo esc_attr__('Vis detaljer', 'kursagenten'); ?>" onclick="toggleAccordion(this)">
                            <div class="text-area">
                                <div class="title-area">
                                    <span class="course-available <?php echo esc_attr($available_class); ?> accordion-icon" title="<?php echo esc_attr($available_text); ?>"></span>
                                    <span class="courselist-title <?php echo esc_attr($available_class); ?>">
                                        <strong class="<?php echo esc_attr($available_class); ?> notranslate" translate="no" title="<?php echo esc_attr($available_text); ?>">
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['location']); ?></span>
                                        </strong>
                                    </span>
                                </div>
                                <div class="content-area">
                                    <?php if (!empty($coursedate['first_date'])) : ?>
                                        <span class="courselist-details"><?php echo esc_html($coursedate['first_date']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($coursedate['course_location_freetext'])) : ?>
                                        <span class="courselist-details notranslate" translate="no">
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['course_location_freetext']); ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($coursedate['time'])) : ?>
                                        <span class="courselist-details"><?php echo esc_html($coursedate['time']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="links-area">
                                <span class="more-info"><?php esc_html_e('Mer info', 'kursagenten'); ?></span>
                                <a class="courselist-button pameldingskjema clickelement" data-url="<?php echo esc_url($coursedate['signup_url']); ?>">
                                    <?php
                                    echo esc_html(kursagenten_get_course_button_label(
                                        (string) ($coursedate['button_text'] ?? ''),
                                        kursagenten_normalize_bool($coursedate['show_registration'] ?? false)
                                    ));
                                    ?>
                                </a>
                            </div>
                        </div>
                        <div class="accordion-content courselist-content">
                            <?php if ($coursedate['missing_first_date']) : ?>
                                <?php
                                $is_online         = has_term('nettbasert', 'ka_course_location', $coursedate['id']);
                                $show_registration = get_post_meta($coursedate['id'], 'ka_course_showRegistrationForm', true);
                                if ($is_online) :
                                    ?>
                                    <p><?php esc_html_e('Etter påmelding vil du få en e-post med mer informasjon om kurset.', 'kursagenten'); ?></p>
                                <?php elseif ($show_registration == '1' || $show_registration === 1 || $show_registration === 'true' || $show_registration === true) : ?>
                                    <p><?php esc_html_e('Du kan melde deg på kurset nå. Etter påmelding vil du få mer informasjon.', 'kursagenten'); ?></p>
                                <?php else : ?>
                                    <p><?php esc_html_e('Det er ikke satt opp dato for nye kurs. Meld din interesse for å få mer informasjon eller å sette deg på venteliste.', 'kursagenten'); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($coursedate['course_isFull']) && $coursedate['course_isFull'] === true) : ?>
                                <p><?php esc_html_e('Kurset er fullt. Du kan melde din interesse for å få mer informasjon eller å sette deg på venteliste.', 'kursagenten'); ?></p>
                            <?php endif; ?>
                            <div class="course-grid col-1-1 compact-courselist-details">
                                <div class="content">
                                    <p>
                                        <?php if (!empty($coursedate['first_date'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Starter:', 'kursagenten'); ?></span>
                                            <span><?php echo esc_html($coursedate['first_date']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['last_date'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Slutter:', 'kursagenten'); ?></span>
                                            <span><?php echo esc_html($coursedate['last_date']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['price'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Pris:', 'kursagenten'); ?></span>
                                            <span><?php echo esc_html($coursedate['price']); ?> <?php echo esc_html($price_posttext); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['location'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Sted:', 'kursagenten'); ?></span>
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['location']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['course_location_room'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Kurslokale:', 'kursagenten'); ?>&nbsp;</span>
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['course_location_room']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['duration'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Varighet:', 'kursagenten'); ?></span>
                                            <span><?php echo esc_html($coursedate['duration']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (in_array('day_schedules', $single_display_fields, true) && !empty($coursedate['day_schedules_count']) && (int) $coursedate['day_schedules_count'] >= 2) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Kursdager:', 'kursagenten'); ?>&nbsp;</span>
                                            <span><?php
                                                echo kursagenten_render_day_schedules_link(
                                                    (int) $coursedate['id'],
                                                    (int) $coursedate['day_schedules_count'],
                                                    $coursedate['course_title'] ?? $coursedate['title'] ?? '',
                                                    ['icon' => '']
                                                );
                                            ?></span><br>
                                        <?php endif; ?>
                                        <?php
                                        $coursedate_instructor_links = $show_instructors && !empty($coursedate['id']) && function_exists('kursagenten_get_course_instructor_links')
                                            ? kursagenten_get_course_instructor_links((int) $coursedate['id'])
                                            : [];
                                        ?>
                                        <?php if ($show_instructors && !empty($coursedate_instructor_links)) : ?>
                                            <span style="font-weight: bold;"><?php echo count($coursedate_instructor_links) === 1 ? esc_html__('Instruktør:', 'kursagenten') : esc_html__('Instruktører:', 'kursagenten'); ?></span>
                                            <span><?php echo implode(', ', $coursedate_instructor_links); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($coursedate['language'])) : ?>
                                            <span style="font-weight: bold;"><?php echo esc_html__('Språk:', 'kursagenten'); ?></span>
                                            <span><?php echo esc_html($coursedate['language']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="aside">
                                    <?php
                                    $coursedate_is_online = false;
                                    if (!empty($coursedate['location'])) {
                                        $location_lower = strtolower($coursedate['location']);
                                        if ($location_lower === 'nettbasert' || stripos($location_lower, 'nettbasert') !== false) {
                                            $coursedate_is_online = true;
                                        }
                                    }
                                    if (!$coursedate_is_online && !empty($coursedate['id'])) {
                                        $coursedate_is_online = has_term('nettbasert', 'ka_course_location', $coursedate['id']);
                                    }
                                    ?>
                                    <?php if (!empty($coursedate['address_street']) && !$coursedate_is_online) : ?>
                                        <p><strong><?php esc_html_e('Adresse', 'kursagenten'); ?></strong></p>
                                        <p>
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['course_location_freetext']); ?></span><br>
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['address_street']); ?></span><br>
                                            <span class="notranslate" translate="no"><?php echo esc_html($coursedate['postal_code']); ?> <?php echo esc_html($coursedate['city']); ?></span><br>
                                            <a style="display: block; padding-top: .4em;" href="https://www.google.com/maps/search/?api=1&query=<?php echo esc_attr($coursedate['address_street']); ?>,+<?php echo esc_attr($coursedate['postal_code']); ?>+<?php echo esc_attr($coursedate['city']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Vis i Google Maps', 'kursagenten'); ?></a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php do_action('ka_singel_courselist_after'); ?>
        </div>
    </div>
    <?php
};

do_action('ka_singel_header_before');
?>

<article class="ka-outer-container course-container course-container-compact<?php echo $has_featured_image ? ' has-course-image' : ''; ?>">
    <?php if ($admin_view === 'true') : ?>
        <div class="edit-course edit-link">
            <a href="<?php echo esc_url('https://kursadmin.kursagenten.no/LegacyIframe/Course/?courseId=' . $course_id); ?>" target="_blank" rel="noopener noreferrer">
                <span class="ka-icon-button"><i class="ka-icon icon-edit"></i></span>
                <span class="edit-text"><?php esc_html_e('Rediger kurs', 'kursagenten'); ?></span>
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($selected_coursedate_data['signup_url'])) : ?>
        <nav class="compact-mobile-nav" aria-label="<?php esc_attr_e('Hurtignavigasjon', 'kursagenten'); ?>">
            <a href="#ka-compact-infostripe"><?php esc_html_e('Detaljer', 'kursagenten'); ?></a>
            <a href="#ka-compact-content"><?php esc_html_e('Informasjon', 'kursagenten'); ?></a>
            <a href="#" class="pameldingskjema clickelement" data-url="<?php echo esc_url($selected_coursedate_data['signup_url']); ?>">
                <?php echo esc_html(kursagenten_get_course_button_label(
                    (string) ($selected_coursedate_data['button_text'] ?? $button_text),
                    kursagenten_normalize_bool($selected_coursedate_data['show_registration'] ?? false)
                )); ?>
            </a>
        </nav>
    <?php endif; ?>

    <header class="ka-section ka-header ka-compact-header ka-highlight-background no-hero-image" id="ka-compact-header">
        <div class="ka-content-container header-content">
            <?php if ($is_parent_course === 'yes') : ?>
                <h1><?php the_title(); ?></h1>
            <?php else : ?>
                <h1><?php echo esc_html($main_course_title); ?></h1>
                <?php if (!empty($display_location) || !empty($selected_coursedate_data['first_date'])) : ?>
                    <p class="compact-header-subtitle">
                        <?php if (!empty($display_location)) : ?>
                            <?php if (!empty($display_location_url)) : ?>
                                <a href="<?php echo esc_url($display_location_url); ?>" class="compact-header-location notranslate" translate="no"><?php echo esc_html($display_location); ?></a>
                            <?php else : ?>
                                <span class="notranslate" translate="no"><?php echo esc_html($display_location); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($selected_coursedate_data['first_date'])) : ?>
                            <span class="compact-header-date"><?php echo esc_html($selected_coursedate_data['first_date']); ?></span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="compact-header-actions">
            
                <div class="header-links iconlist horizontal uppercase">
                <?php if (!empty($selected_coursedate_data['signup_url'])) : ?>
                    <div class="course-buttons" id="ka-compact-signup">
                        <button type="button" class="button pameldingskjema clickelement" data-url="<?php echo esc_url($selected_coursedate_data['signup_url']); ?>">
                            <?php echo esc_html(kursagenten_get_course_button_label(
                                (string) ($selected_coursedate_data['button_text'] ?? $button_text),
                                kursagenten_normalize_bool($selected_coursedate_data['show_registration'] ?? false)
                            )); ?>
                        </button>
                    </div>
                <?php endif; ?>
                    <?php
                    $kurs_url = Designmaler::get_system_page_url('kurs', true);
                    if (!empty($kurs_url)) :
                        ?>
                        <div><a href="<?php echo esc_url($kurs_url); ?>"><i class="ka-icon icon-vertical-bars"></i> <?php esc_html_e('Alle kurs', 'kursagenten'); ?></a></div>
                    <?php endif; ?>
                    <div class="taxonomy-list horizontal">
                        <?php if (!empty($coursecategory_links)) : ?>
                            <i class="ka-icon icon-vertical-bars"></i><?php echo implode('<span class="separator">|</span>', $coursecategory_links); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php do_action('ka_singel_header_links_after'); ?>

                
            </div>
        </div>
    </header>

    <?php do_action('ka_singel_header_after'); ?>

    <section class="ka-section ka-infostripe ka-highlight-background" id="ka-compact-infostripe">
        <div class="ka-content-container">
            <div class="compact-infostripe-grid">
                <div class="compact-infostripe-col compact-infostripe-times">
                    <div class="compact-infostripe-times-body">
                        <h2 class="small"><?php esc_html_e('Kurstider', 'kursagenten'); ?></h2>
                        <div class="iconlist medium">
                            <?php if (in_array('first_date', $single_display_fields, true) && !empty($selected_coursedate_data['first_date'])) : ?>
                                <div>
                                    <i class="ka-icon icon-calendar"></i>
                                    <span>
                                        <?php echo esc_html($selected_coursedate_data['first_date']); ?>
                                        <?php if (in_array('last_date', $single_display_fields, true) && !empty($selected_coursedate_data['last_date']) && $selected_coursedate_data['last_date'] !== $selected_coursedate_data['first_date']) : ?>
                                            &ndash; <?php echo esc_html($selected_coursedate_data['last_date']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if (in_array('day_schedules', $single_display_fields, true) && !empty($selected_coursedate_data['day_schedules_count']) && (int) $selected_coursedate_data['day_schedules_count'] >= 2) : ?>
                                <div>
                                    <i class="ka-icon icon-calendar"></i>
                                    <span><?php echo esc_html__('Kursdager:', 'kursagenten'); ?>
                                        <?php
                                        echo kursagenten_render_day_schedules_link(
                                            (int) ($selected_coursedate_data['id'] ?? 0),
                                            (int) $selected_coursedate_data['day_schedules_count'],
                                            $selected_coursedate_data['title'] ?? get_the_title(),
                                            ['icon' => '']
                                        );
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if (in_array('time', $single_display_fields, true) && !empty($selected_coursedate_data['time'])) : ?>
                                <div><i class="ka-icon icon-time"></i><span><?php echo esc_html($selected_coursedate_data['time']); ?></span></div>
                            <?php endif; ?>
                            <?php if (in_array('duration', $single_display_fields, true) && !empty($selected_coursedate_data['duration'])) : ?>
                                <div><i class="ka-icon icon-stopwatch"></i><span><?php echo esc_html($selected_coursedate_data['duration']); ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($show_courselist_toggle && !empty($all_coursedates)) : ?>
                        <p class="compact-more-dates">
                            <button type="button"
                                    class="compact-more-dates-link"
                                    id="ka-compact-more-dates-trigger"
                                    data-compact-panel-toggle
                                    data-label-open="<?php echo esc_attr__('Se flere datoer og steder', 'kursagenten'); ?>"
                                    data-label-close="<?php echo esc_attr__('Lukk panel', 'kursagenten'); ?>"
                                    aria-expanded="false"
                                    aria-controls="ka-compact-courselist-panel">
                                <?php esc_html_e('Se flere datoer og steder', 'kursagenten'); ?> &rarr;
                            </button>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="compact-infostripe-col compact-infostripe-location">
                    <h2 class="small"><?php esc_html_e('Sted', 'kursagenten'); ?></h2>
                    <div class="iconlist medium">
                        <?php if (!empty($display_location)) : ?>
                            <div><i class="ka-icon icon-location"></i><span><?php echo esc_html__('Sted:', 'kursagenten'); ?> <span class="notranslate" translate="no"><?php echo esc_html($display_location); ?></span></span></div>
                        <?php endif; ?>
                        <?php if (in_array('room', $single_display_fields, true) && !empty($selected_coursedate_data['course_location_room'])) : ?>
                            <div><i class="ka-icon icon-bag"></i><span><?php echo esc_html__('Lokale:', 'kursagenten'); ?> <span class="notranslate" translate="no"><?php echo esc_html($selected_coursedate_data['course_location_room']); ?></span></span></div>
                        <?php endif; ?>
                        <?php if (!empty($selected_coursedate_data['location_freetext']) && $selected_coursedate_data['location_freetext'] !== $display_location) : ?>
                            <div><i class="ka-icon icon-location"></i><span class="notranslate" translate="no"><?php echo esc_html($selected_coursedate_data['location_freetext']); ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="compact-infostripe-col compact-infostripe-other">
                    <h2 class="small"><?php esc_html_e('Annet', 'kursagenten'); ?></h2>
                    <div class="iconlist medium">
                        <?php if (in_array('price', $single_display_fields, true) && !empty($selected_coursedate_data['price'])) : ?>
                            <div><i class="ka-icon icon-bag"></i><span><?php echo esc_html__('Pris:', 'kursagenten'); ?> <?php echo esc_html($selected_coursedate_data['price']); ?> <?php echo esc_html($price_posttext); ?></span></div>
                        <?php endif; ?>
                        <?php if (in_array('language', $single_display_fields, true) && !empty($selected_coursedate_data['language'])) : ?>
                            <div><i class="ka-icon icon-chat-bubble"></i><span><?php echo esc_html__('Språk:', 'kursagenten'); ?> <?php echo esc_html($selected_coursedate_data['language']); ?></span></div>
                        <?php endif; ?>
                        <?php if ($show_contact_in_infobar) : ?>
                            <div class="compact-contact-item">
                                <i class="ka-icon icon-user"></i>
                                <span>
                                    <?php echo esc_html__('Kontaktperson:', 'kursagenten'); ?>
                                    <?php if (!empty($contact_name)) : ?><?php echo esc_html($contact_name); ?><?php endif; ?>
                                    <?php if (!empty($contact_phone)) : ?><br><?php echo esc_html($contact_phone); ?><?php endif; ?>
                                    <?php if (!empty($contact_email)) : ?><br><a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a><?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php do_action('ka_singel_nextcourse_after'); ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($show_courselist_toggle && !empty($all_coursedates)) : ?>
        <section class="ka-section compact-infostripe-panel ka-highlight-background"
                 id="ka-compact-courselist-panel"
                 hidden
                 aria-labelledby="ka-compact-more-dates-trigger">
            <div class="ka-content-container">
                <?php $render_courselist(['panel_mode' => true, 'expand_first' => true]); ?>
                <p class="compact-more-dates compact-more-dates--panel-close" hidden>
                    <button type="button"
                            class="compact-more-dates-link compact-more-dates-link--close"
                            data-compact-panel-toggle
                            data-label-close="<?php echo esc_attr__('Lukk panel', 'kursagenten'); ?>"
                            aria-expanded="true"
                            aria-controls="ka-compact-courselist-panel">
                        &larr; <?php esc_html_e('Lukk panel', 'kursagenten'); ?>
                    </button>
                </p>
            </div>
        </section>
        <script>
        // Self-contained: open + scroll to the courselist panel when arriving from a
        // location tab (?ka_open_panel=1). Runs during parse, so it does not depend on
        // the main script which can load late behind blocking footer scripts.
        (function () {
            var params;
            try {
                params = new URLSearchParams(window.location.search);
            } catch (e) {
                return;
            }
            if (params.get('ka_open_panel') !== '1') {
                return;
            }

            // Reveal the panel right away (its markup precedes this inline script).
            var panel = document.getElementById('ka-compact-courselist-panel');
            if (panel) {
                panel.hidden = false;
            }
            var closeWrap = document.querySelector('.compact-more-dates--panel-close');
            if (closeWrap) {
                closeWrap.hidden = false;
            }

            // Remove the param so it does not linger in the address bar.
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('ka_open_panel');
                window.history.replaceState(null, '', url.pathname + url.search + url.hash);
            } catch (e) {}

            // Expand the first row and scroll once the layout has settled (after images).
            window.addEventListener('load', function () {
                if (panel) {
                    var firstItem = panel.querySelector('.compact-courselist-expand-first');
                    var firstMain = firstItem ? firstItem.querySelector('.courselist-main') : null;
                    if (firstMain && firstItem && !firstItem.classList.contains('active') && typeof window.toggleAccordion === 'function') {
                        window.toggleAccordion(firstMain);
                    }
                }
                var infostripe = document.getElementById('ka-compact-infostripe');
                if (infostripe) {
                    infostripe.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        })();
        </script>
    <?php endif; ?>

    <section class="ka-section course-information" id="ka-compact-content">
        <div class="ka-content-container">
            <div class="course-grid compact-main-grid">
                <div class="content">
                    <?php do_action('ka_singel_content_intro_before'); ?>
                    <?php if (has_excerpt()) : ?>
                        <div class="excerpt"><?php the_excerpt(); ?></div>
                    <?php endif; ?>
                    <?php do_action('ka_singel_content_intro_after'); ?>

                    <?php do_action('ka_singel_content_before'); ?>
                    <?php if (!empty($content)) : ?>
                        <div class="course-meta-content"><?php echo wpautop(wp_kses_post($content)); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($selected_coursedate_data['signup_url'])) : ?>
                        <div class="content-buttons">
                            <button type="button" class="button pameldingskjema clickelement" data-url="<?php echo esc_url($selected_coursedate_data['signup_url']); ?>">
                                <?php echo esc_html(kursagenten_get_course_button_label(
                                    (string) ($selected_coursedate_data['button_text'] ?? $button_text),
                                    kursagenten_normalize_bool($selected_coursedate_data['show_registration'] ?? false)
                                )); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php do_action('ka_singel_content_after'); ?>
                </div>

                <?php $render_course_image(); ?>

                <aside class="aside compact-aside">
                    <?php do_action('ka_singel_aside_before'); ?>

                    <div class="compact-similar-courses">
                        <h2 class="small"><?php esc_html_e('Lignende kurs', 'kursagenten'); ?></h2>
                        <?php
                        $archive_show_images = get_option('kursagenten_show_images', 'yes');
                        $related_bildestr    = ($archive_show_images === 'yes') ? '52px' : '0px';
                        echo do_shortcode('[kurs-i-samme-kategori overskrift="h4" layout="rad" grid="1" gridtablet="1" gridmobil="1" bildestr="' . esc_attr($related_bildestr) . '" bildeformat="1/1" bildeform=avrundet fontmin="13px" fontmaks="15px" avstand="0" radavstand=".9em" limit="6"]');
                        ?>
                    </div>
                    <?php do_action('ka_singel_aside_after'); ?>
                </aside>
            </div>

            <?php if (!empty($wp_content)) : ?>
                <div class="compact-wp-content wp-content">
                    <?php if ($admin_view === 'true') : ?>
                        <div class="edit-link">
                            <a href="<?php echo esc_url(get_edit_post_link()); ?>">
                                <i class="ka-icon icon-edit"></i>
                                <span class="edit-text"><?php esc_html_e('Rediger Wordpress innhold', 'kursagenten'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="content-text<?php echo esc_attr($admin_view_class); ?>"><?php echo apply_filters('the_content', $wp_content); ?></div>
                </div>
            <?php elseif ($admin_view === 'true') : ?>
                <div class="compact-wp-content wp-content">
                    <div class="edit-link">
                        <a href="<?php echo esc_url(get_edit_post_link()); ?>">
                            <i class="ka-icon icon-plus"></i>
                            <span class="edit-text"><?php esc_html_e('Legg til ekstra Wordpress innhold', 'kursagenten'); ?></span>
                        </a>
                    </div>
                    <div class="content-text<?php echo esc_attr($admin_view_class); ?>"></div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php do_action('ka_singel_footer_before'); ?>
    <section class="ka-section ka-compact-bottom ka-highlight-background" id="ka-compact-bottom">
        <div class="ka-content-container">
            <div class="compact-bottom-more">
                <h2 class="small"><?php esc_html_e('Flere kurs', 'kursagenten'); ?></h2>
                <?php
                $archive_show_images = get_option('kursagenten_show_images', 'yes');
                $related_bildestr    = ($archive_show_images === 'yes') ? '72px' : '0px';
                echo do_shortcode('[kurskategorier overskrift="h4" layout="rad" grid="4" gridtablet="3" gridmobil="2" bildestr="' . esc_attr($related_bildestr) . '" bildeformat="1/1" bildeform=firkantet fontmin="13px" fontmaks="15px" avstand="0" radavstand=".85em"]');
                ?>
            </div>
        </div>
    </section>
    <?php do_action('ka_singel_footer_after'); ?>
</article>

<?php do_action('ka_singel_after'); ?>
