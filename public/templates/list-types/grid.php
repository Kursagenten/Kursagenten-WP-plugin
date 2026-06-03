<?php

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

// Sjekk visningstype fra args
$view_type = isset($args['view_type']) ? $args['view_type'] : 'all_coursedates';
$is_taxonomy_page = isset($args['is_taxonomy_page']) && $args['is_taxonomy_page'];

// Sjekk om vi skal tvinge standard visning (fra kortkode)
$force_standard_view = isset($args['force_standard_view']) && $args['force_standard_view'] === true;

// Hvis visningstype er 'main_courses', vis hovedkurs med første tilgjengelige dato
if ($view_type === 'main_courses' && !$force_standard_view) {
    // Når view_type er 'main_courses', returnerer queryen ka_coursedate posts
    // Vi må finne hovedkurset basert på kursdatoen
    $coursedate_id = get_the_ID();
    
    // Hent main_course_id fra kursdatoen
    $main_course_id = get_post_meta($coursedate_id, 'ka_main_course_id', true);
    
    // Finn hovedkurset basert på main_course_id
    $main_courses = get_posts([
        'post_type' => 'ka_course',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'ka_main_course_id',
                'value' => $main_course_id,
                'compare' => '='
            ],
            [
                'key' => 'ka_is_parent_course',
                'value' => 'yes',
                'compare' => '='
            ]
        ]
    ]);
    
    // Hvis vi fant hovedkurset, bruk det som base
    if (!empty($main_courses)) {
        $course_id = $main_courses[0]->ID;
        $course_title = get_the_title($course_id);
        $excerpt = get_the_excerpt($course_id);
    } else {
        // Fallback hvis hovedkurset ikke finnes
        $course_id = 0;
        $course_title = '';
        $excerpt = '';
    }
    
    // Check if we need to filter by location (taxonomy page)
    $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : null;
    $current_term = isset($args['current_term']) ? $args['current_term'] : null;
    
    // Build meta query for coursedates
    $meta_query = [
        ['key' => 'ka_main_course_id', 'value' => $main_course_id],
    ];
    
    // If on a location taxonomy page, filter coursedates by that location
    if ($taxonomy === 'ka_course_location' && $current_term) {
        $meta_query[] = [
            'key' => 'ka_course_location',
            'value' => $current_term->name,
            'compare' => '='
        ];
    }
    
    $related_coursedates = get_posts([
        'post_type' => 'ka_coursedate',
        'posts_per_page' => -1,
        'meta_query' => $meta_query,
    ]);
    
    // Konverter til array av IDer
    $related_coursedate_ids = array_map(function($post) {
        return $post->ID;
    }, $related_coursedates);
    
    // Hent data fra første tilgjengelige kursdato
    $selected_coursedate_data = get_selected_coursedate_data($related_coursedate_ids);
    
    // Hent lokasjonsinformasjon fra den valgte kursdatoen
    $location = $selected_coursedate_data['location'] ?? '';
    $location_freetext = $selected_coursedate_data['location_freetext'] ?? '';
    $location_room = $selected_coursedate_data['course_location_room'] ?? '';
    
    // Hent plassholderbilde fra innstillinger
    $options = get_option('design_option_name');
    $placeholder_image = !empty($options['ka_plassholderbilde_kurs']) 
        ? $options['ka_plassholderbilde_kurs']
        : rtrim(KURSAG_PLUGIN_URL, '/') . '/assets/images/placeholder-kurs.jpg';
    
    // Grid cards are image-forward: use WordPress `medium` on all viewports (not `thumbnail`).
    $featured_image_card = $course_id ? get_the_post_thumbnail_url($course_id, 'medium') : '';
    $featured_image_card = $featured_image_card ?: $placeholder_image;
    
    $course_link_context_coursedate_id = (int) ($selected_coursedate_data['id'] ?? $coursedate_id);
    $course_link_context_course_id = (int) $course_id;

    // Sett opp link til kurset - finn lokasjonsundersiden basert på valgt kursdato
    $course_link = $course_id ? get_permalink($course_id) : '#'; // Fallback til hovedkurset
    
    // Hvis vi har en valgt kursdato, prøv å finne lokasjonsundersiden
    if (!empty($selected_coursedate_data['id'])) {
        $selected_coursedate_id = $selected_coursedate_data['id'];
        $coursedate_location_id = get_post_meta($selected_coursedate_id, 'ka_location_id', true);
        $coursedate_main_course_id = get_post_meta($selected_coursedate_id, 'ka_main_course_id', true);
        
        // Finn lokasjonsundersiden (subcourse) basert på location_id og main_course_id
        if (!empty($coursedate_location_id) && !empty($coursedate_main_course_id)) {
            $sub_course = get_posts([
                'post_type' => 'ka_course',
                'posts_per_page' => 1,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'ka_location_id',
                        'value' => $coursedate_location_id,
                        'compare' => '='
                    ],
                    [
                        'key' => 'ka_main_course_id',
                        'value' => $coursedate_main_course_id,
                        'compare' => '='
                    ],
                    // Sjekk at is_parent_course IKKE eksisterer eller ikke er 'yes'
                    [
                        'key' => 'ka_is_parent_course',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ]);
            
            // Bruk lokasjonsundersiden hvis den finnes, ellers fallback til hovedkurset
            if (!empty($sub_course)) {
                $course_link = get_permalink($sub_course[0]->ID);
                $course_link_context_course_id = (int) $sub_course[0]->ID;
            }
        }
    }
    
    // Hent data fra første tilgjengelige kursdato
    $first_course_date = $selected_coursedate_data['first_date'] ?? '';
    $last_course_date = $selected_coursedate_data['last_date'] ?? '';
    $first_course_date_raw = $selected_coursedate_data['first_date_raw'] ?? '';
    $last_course_date_raw = $selected_coursedate_data['last_date_raw'] ?? '';
    $registration_deadline = $selected_coursedate_data['registration_deadline'] ?? '';
    $price = $selected_coursedate_data['price'] ?? '';
    $after_price = $selected_coursedate_data['after_price'] ?? '';
    $duration = $selected_coursedate_data['duration'] ?? '';
    $coursetime = $selected_coursedate_data['time'] ?? '';
    $button_text = $selected_coursedate_data['button_text'] ?? '';
    $signup_url = $selected_coursedate_data['signup_url'] ?? '';
    $is_full = kursagenten_normalize_bool($selected_coursedate_data['is_full'] ?? false);
    $show_registration = kursagenten_normalize_bool($selected_coursedate_data['show_registration'] ?? false);
    $day_schedules_count = (int) ($selected_coursedate_data['day_schedules_count'] ?? 0);
    $day_schedules_coursedate_id = (int) ($selected_coursedate_data['id'] ?? 0);
} else {
    // Original kode for coursedates
    $course_id = get_the_ID();

    $course_title =             get_post_meta($course_id, 'ka_course_title', true);
    $first_course_date_raw =    get_post_meta($course_id, 'ka_course_first_date', true);
    $last_course_date_raw =     get_post_meta($course_id, 'ka_course_last_date', true);
    $first_course_date =        ka_format_date($first_course_date_raw);
    $last_course_date =         ka_format_date($last_course_date_raw);
    $registration_deadline =    ka_format_date(get_post_meta($course_id, 'ka_course_registration_deadline', true));
    $duration =                 get_post_meta($course_id, 'ka_course_duration', true);
    $coursetime =               get_post_meta($course_id, 'ka_course_time', true);
    $price =                    get_post_meta($course_id, 'ka_course_price', true);
    $after_price =              get_post_meta($course_id, 'ka_course_text_after_price', true);
    $location =                 get_post_meta($course_id, 'ka_course_location', true);
    $location_freetext =        get_post_meta($course_id, 'ka_course_location_freetext', true);
    $location_room =            get_post_meta($course_id, 'ka_course_location_room', true);
    $is_full_meta =             get_post_meta($course_id, 'ka_course_isFull', true);
    $marked_as_full_meta =      get_post_meta($course_id, 'ka_course_markedAsFull', true);
    $is_full =                  kursagenten_normalize_bool($is_full_meta) || kursagenten_normalize_bool($marked_as_full_meta);
    $show_registration_meta =   get_post_meta($course_id, 'ka_course_showRegistrationForm', true);
    $show_registration =        kursagenten_normalize_bool($show_registration_meta);

    $button_text =              get_post_meta($course_id, 'ka_course_button_text', true);
    $signup_url =               ka_get_coursedate_signup_url($course_id);

    $related_course_id =        get_post_meta($course_id, 'ka_location_id', true);
    $main_course_id =           get_post_meta($course_id, 'ka_main_course_id', true);
    $day_schedules_count =      (int) get_post_meta($course_id, 'ka_course_day_schedules_count', true);
    $day_schedules_coursedate_id = (int) $course_id;
    $course_link_context_coursedate_id = (int) $course_id;
    $course_link_context_course_id = 0;

    $related_course_info = get_course_info_by_location($related_course_id, $main_course_id);

    // Hent plassholderbilde fra innstillinger
    $options = get_option('design_option_name');
    $placeholder_image = !empty($options['ka_plassholderbilde_kurs']) 
        ? $options['ka_plassholderbilde_kurs']
        : rtrim(KURSAG_PLUGIN_URL, '/') . '/assets/images/placeholder-kurs.jpg';
    
    if ($related_course_info) {
        $course_link = esc_url($related_course_info['permalink']);
        $course_link_context_course_id = (int) ($related_course_info['id'] ?? 0);
        $featured_image_card = $related_course_info['thumbnail-medium']
            ?: ($related_course_info['thumbnail-full'] ?? '')
            ?: ($related_course_info['thumbnail'] ?? '')
            ?: $placeholder_image;
        $excerpt = $related_course_info['excerpt'];
    } else {
        // Hvis ingen relatert kursinfo, sett fallback-verdier
        $course_link = false;
        $featured_image_card = $placeholder_image;
        $excerpt = '';
    }
}

$course_count = $course_count ?? 0;
$item_class = $course_count === 1 ? ' single-item' : '';
$list_display = kursagenten_get_list_display_fields($args);
$list_item_links = ka_resolve_course_list_links(
    (string) $course_link,
    (int) ($course_link_context_coursedate_id ?? 0),
    (int) ($course_link_context_course_id ?? 0),
    (string) ($signup_url ?? '')
);
$course_link = $list_item_links['course_link'];
$signup_url = $list_item_links['signup_url'];
$course_link_target_attrs = ka_get_external_link_target_attributes($course_link);

$resolved_taxonomy = '';
if (isset($args['taxonomy']) && is_string($args['taxonomy'])) {
    $resolved_taxonomy = sanitize_text_field($args['taxonomy']);
}
if ($resolved_taxonomy === '') {
    $queried_object = get_queried_object();
    if (is_object($queried_object) && isset($queried_object->taxonomy) && is_string($queried_object->taxonomy)) {
        $resolved_taxonomy = $queried_object->taxonomy;
    }
}
$is_taxonomy_context = !$force_standard_view && ($is_taxonomy_page || $resolved_taxonomy !== '');

// Sjekk om bilder skal vises
// Prioritet: shortcode attributt > taksonomi-spesifikk innstilling > global innstilling
$shortcode_show_images = isset($args['shortcode_show_images']) ? $args['shortcode_show_images'] : null;

// If shortcode explicitly sets bilder parameter to 'yes' or 'no', use it
if ($shortcode_show_images === 'yes' || $shortcode_show_images === 'no') {
    // Bruk shortcode attributt hvis eksplisitt satt til yes eller no
    $show_images = $shortcode_show_images;
} elseif ($is_taxonomy_context) {
    // Taksonomi-side: bruk taksonomi-innstillinger med proper override handling.
    if ($resolved_taxonomy !== '') {
        $show_images = get_taxonomy_setting($resolved_taxonomy, 'show_images', 'yes');
    } else {
        $show_images = get_option('kursagenten_show_images_taxonomy', 'yes');
    }
} else {
    // Standard: bruk global innstilling
    $show_images = get_option('kursagenten_show_images', 'yes');
}

$with_image_class = $show_images === 'yes' ? ' with-image' : '';

if ($is_taxonomy_context) {
    if ($resolved_taxonomy !== '') {
        $buttons_display_option = get_taxonomy_setting($resolved_taxonomy, 'buttons_display', 'show_buttons');
    } else {
        $buttons_display_option = get_option('kursagenten_taxonomy_buttons_display', 'show_buttons');
    }
} else {
    $buttons_display_option = get_option('kursagenten_archive_buttons_display', 'show_buttons');
}
$buttons_display_override = isset($args['buttons_display']) ? sanitize_text_field((string) $args['buttons_display']) : '';
if (in_array($buttons_display_override, ['show_buttons', 'signup_link'], true)) {
    $buttons_display_option = $buttons_display_override;
}
$show_signup_link_only = ($buttons_display_option === 'signup_link');

// Hent instruktører for kurset
$instructors = get_the_terms($course_id, 'ka_instructors');
$instructor_links = [];
if (!empty($instructors) && !is_wp_error($instructors)) {
    $instructor_links = array_map(function ($term) {
        $instructor_url = get_instructor_display_url($term, 'ka_instructors');
        $display_name = function_exists('get_instructor_display_name') ? get_instructor_display_name($term) : $term->name;
        return '<a href="' . esc_url($instructor_url) . '"><span class="notranslate" translate="no">' . esc_html($display_name) . '</span></a>';
    }, $instructors);
}

?>
<?php
// Hent kurskategorier for data-category attributt
$course_categories = get_the_terms($course_id, 'ka_coursecategory');
$category_slugs = [];
if (!empty($course_categories) && !is_wp_error($course_categories)) {
    foreach ($course_categories as $category) {
        // Bruk kun den faktiske kategorien kurset tilhører
        $category_slugs[] = $category->slug;
    }
}
$category_slugs = array_unique($category_slugs);

// Generate view type class
$view_type_class = ' view-type-' . str_replace('_', '', $view_type);
?>
<div class="courselist-item grid-item<?php echo $item_class . $view_type_class; ?>" data-location="<?php echo esc_attr($location_freetext); ?>" data-category="<?php echo esc_attr(implode(' ', $category_slugs)); ?>">
    <div class="courselist-card<?php echo $with_image_class; ?>">
        <?php if ($show_images === 'yes') : ?>
        <!-- Image area -->
        <div class="card-image" data-ka-bg-url="<?php echo esc_attr( esc_url( $featured_image_card ) ); ?>" data-bg="<?php echo esc_attr( esc_url( $featured_image_card ) ); ?>" style="background-image: url('<?php echo esc_url( $featured_image_card ); ?>');">
            <a class="image-inner" href="<?php echo esc_url($course_link); ?>"<?php echo $course_link_target_attrs; ?> title="<?php echo esc_attr($course_title); ?>" aria-label="Se kurs: <?php echo esc_attr($course_title); ?>">
                <span class="sr-only">Se kurs: <?php echo esc_html($course_title); ?></span>
            </a>
            <?php if ($is_full) : ?>
                <span class="card-availability course-available ka-full">Fullt</span>
            <?php elseif (!$show_registration) : ?>
                <span class="card-availability course-available ka-on-demand">På forespørsel</span>
            <?php else : ?>
                <span class="card-availability course-available">Ledige plasser</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="card-content">
            <div class="card-content-upper">
                <!-- Title area -->
                <div class="title-area">
                    <h3 class="course-title">
                        <a href="<?php echo esc_url($course_link); ?>"<?php echo $course_link_target_attrs; ?> class="course-link"><?php echo esc_html($course_title); ?></a>
                    </h3>
                    <?php if ($show_images === 'no') : ?>
                    <?php if ($is_full) : ?>
                        <div class="course-availability ka-tooltip ka-tooltip-left" data-title="Fullt">
                            <span class="card-availability course-available ka-full"></span>
                        </div>
                    <?php elseif (!$show_registration) : ?>
                        <div class="course-availability ka-tooltip ka-tooltip-left" data-title="På forespørsel">
                            <span class="card-availability course-available ka-on-demand"></span>
                        </div>
                    <?php else : ?>
                        <div class="course-availability ka-tooltip ka-tooltip-left" data-title="Ledige plasser">
                            <span class="card-availability course-available"></span>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Location -->
                <?php
                $list_meta_tooltips = kursagenten_get_list_meta_tooltips();
                $is_location_taxonomy = ($resolved_taxonomy === 'ka_course_location');
                $show_location_name = !empty($list_display['location']) && !$is_location_taxonomy && !empty($location);
                $show_location_freetext = !empty($list_display['location_freetext']) && !empty($location_freetext);
                $show_location_room = !empty($list_display['room']) && !empty($location_room);
                $show_location_block = $is_location_taxonomy
                    ? $show_location_freetext
                    : ($show_location_name || $show_location_freetext || $show_location_room);
                ?>
                <?php if ($show_location_block) : ?>
                <div class="card-location">
                    <strong class="notranslate" translate="no">
                        <?php if ($show_location_name) : ?>
                            <?php echo esc_html($location); ?>
                        <?php endif; ?>
                        <?php if ($show_location_freetext) : ?>
                            <?php if ($is_location_taxonomy) : ?>
                                <span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span>
                            <?php else : ?>
                                <?php if ($show_location_name) : ?> <?php endif; ?>
                                (<span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span>)
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!$is_location_taxonomy && $show_location_room) : ?>
                            <?php if ($show_location_name || $show_location_freetext) : ?> - <?php endif; ?>
                            <span class="notranslate ka-cursor-tooltip ka-cursor-tooltip--help" translate="no" data-title="<?php echo esc_attr($list_meta_tooltips['room']); ?>"><?php echo esc_html($location_room); ?></span>
                        <?php endif; ?>
                    </strong>
                </div>
                <?php endif; ?>
                
                <!-- Excerpt -->
                <?php if (!empty($excerpt)) : ?>
                <div class="card-excerpt">
                    <?php echo wp_trim_words(wp_kses_post($excerpt), 20, '...'); ?>
                </div>
                <?php endif; ?>
                
                <!-- Course details -->
                <div class="card-details">
                    <ul class="card-details-list">
                        <?php if ($view_type === 'main_courses' && !$force_standard_view) : ?>
                            <?php
                            $list_date_text = kursagenten_list_format_course_dates(
                                $first_course_date,
                                $last_course_date ?? '',
                                !empty($list_display['last_date']),
                                $first_course_date_raw ?? '',
                                $last_course_date_raw ?? ''
                            );
                            ?>
                            <?php if ($list_date_text !== '') : ?>
                            <li>
                                <i class="ka-icon icon-calendar"></i>
                                <span class="ka-main-color">Neste kurs: </span><?php echo esc_html($list_date_text); ?>
                                <?php if (count($related_coursedate_ids) > 1) : ?>
                                    <a href="#" class="show-ka-modal" data-course-id="<?php echo esc_attr($course_id); ?>" style="margin-left: 8px; font-size: 0.9em;">
                                        (+<?php echo count($related_coursedate_ids) - 1; ?> flere)
                                    </a>
                                <?php endif; ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($list_display['registration_deadline'] && !empty($registration_deadline)) : ?>
                            <li class="ka-cursor-tooltip ka-cursor-tooltip--help" data-title="<?php echo esc_attr($list_meta_tooltips['registration_deadline']); ?>"><i class="ka-icon icon-alarmclock"></i><?php echo esc_html($registration_deadline); ?></li>
                            <?php endif; ?>
                            <?php if ($list_display['time'] && !empty($coursetime)) : ?>
                            <li>
                                <i class="ka-icon icon-time"></i>
                                <?php echo esc_html($coursetime); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($list_display['day_schedules']) && $day_schedules_count >= 2 && $day_schedules_coursedate_id > 0) : ?>
                            <li class="day-schedules"><?php
                                echo kursagenten_render_day_schedules_link(
                                    $day_schedules_coursedate_id,
                                    $day_schedules_count,
                                    $course_title,
                                    [
                                        'icon'           => 'icon-calendar',
                                        'cursor_tooltip' => $list_meta_tooltips['day_schedules'],
                                    ]
                                );
                            ?></li>
                            <?php endif; ?>
                            <?php if ($list_display['instructor'] && !empty($instructor_links)) : ?>
                            <li><i class="ka-icon icon-user"></i><?php echo implode('', $instructor_links); ?></li>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php
                            $list_date_text = kursagenten_list_format_course_dates(
                                $first_course_date,
                                $last_course_date ?? '',
                                !empty($list_display['last_date']),
                                $first_course_date_raw ?? '',
                                $last_course_date_raw ?? ''
                            );
                            ?>
                            <?php if ($list_date_text !== '') : ?>
                            <li><i class="ka-icon icon-calendar"></i><?php echo esc_html($list_date_text); ?></li>
                            <?php endif; ?>
                            <?php if ($list_display['registration_deadline'] && !empty($registration_deadline)) : ?>
                            <li class="ka-cursor-tooltip ka-cursor-tooltip--help" data-title="<?php echo esc_attr($list_meta_tooltips['registration_deadline']); ?>"><i class="ka-icon icon-alarmclock"></i><?php echo esc_html($registration_deadline); ?></li>
                            <?php endif; ?>
                            <?php if ($list_display['time'] && !empty($coursetime)) : ?>
                            <li><i class="ka-icon icon-time"></i><?php echo esc_html($coursetime); ?></li>
                            <?php endif; ?>
                            <?php if (!empty($list_display['day_schedules']) && $day_schedules_count >= 2 && $day_schedules_coursedate_id > 0) : ?>
                            <li class="day-schedules"><?php
                                echo kursagenten_render_day_schedules_link(
                                    $day_schedules_coursedate_id,
                                    $day_schedules_count,
                                    $course_title,
                                    [
                                        'icon'           => 'icon-calendar',
                                        'cursor_tooltip' => $list_meta_tooltips['day_schedules'],
                                    ]
                                );
                            ?></li>
                            <?php endif; ?>
                            <?php if ($list_display['instructor'] && !empty($instructor_links)) : ?>
                            <li><i class="ka-icon icon-user"></i><?php echo implode(', ', $instructor_links); ?></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card-content-lower">
                <div class="card-separator"></div>
                
                <!-- Footer area -->
                <div class="card-footer">
                    <?php if ($view_type === 'main_courses' && !$force_standard_view) : ?>
                        <?php if ($list_display['price'] && !empty($price)) : ?>
                        <div class="card-price">
                            <strong><?php echo esc_html($price); ?> <?php echo isset($after_price) ? esc_html($after_price) : ''; ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($show_signup_link_only) : ?>
                            <a class="pamelding signup-link pameldingskjema" data-url="<?php echo esc_url($signup_url); ?>">
                                <?php echo esc_html($button_text ?: 'Påmelding'); ?> <i class="ka-icon icon-arrow-right-short"></i>
                            </a>
                        <?php else : ?>
                            <button class="courselist-button pamelding pameldingsknapp pameldingskjema" data-url="<?php echo esc_url($signup_url); ?>">
                                <?php echo esc_html($button_text ?: 'Påmelding'); ?>
                            </button>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php if ($list_display['price'] && !empty($price)) : ?>
                        <div class="card-price">
                            <strong><?php echo esc_html($price); ?> <?php echo isset($after_price) ? esc_html($after_price) : ''; ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($show_signup_link_only) : ?>
                            <a class="pamelding signup-link pameldingskjema" data-url="<?php echo esc_url($signup_url); ?>">
                                <?php echo esc_html($button_text ?: 'Påmelding'); ?> <i class="ka-icon icon-arrow-right-short"></i>
                            </a>
                        <?php else : ?>
                            <button class="courselist-button pamelding pameldingsknapp pameldingskjema" data-url="<?php echo esc_url($signup_url); ?>">
                                <?php echo esc_html($button_text ?: 'Påmelding'); ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($view_type === 'main_courses' && !$force_standard_view && count($related_coursedate_ids) > 1) : ?>
    <!-- Popup for alle kursdatoer -->
    <div class="ka-course-dates-modal" id="modal-<?php echo esc_attr($course_id); ?>" style="display: none;">
        <div class="ka-modal-overlay"></div>
        <div class="ka-modal-content">
            <div class="ka-modal-header">
                <h3><?php echo esc_html($course_title); ?></h3>
                <button class="ka-modal-close" aria-label="Lukk">&times;</button>
            </div>
            <div class="ka-modal-body">
                <h4>Alle tilgjengelige kurssteder og datoer</h4>
                <?php
                // Hent main_course_id for å finne alle kursdatoer
                $main_course_id = get_post_meta($course_id, 'ka_main_course_id', true);
                if (empty($main_course_id)) {
                    $main_course_id = get_post_meta($course_id, 'ka_location_id', true);
                }
                
                // Use the filtered coursedates if on a location taxonomy page
                $modal_meta_query = [
                    ['key' => 'ka_main_course_id', 'value' => $main_course_id],
                ];
                
                // If on a location taxonomy page, filter modal coursedates by that location
                if ($taxonomy === 'ka_course_location' && $current_term) {
                    $modal_meta_query[] = [
                        'key' => 'ka_course_location',
                        'value' => $current_term->name,
                        'compare' => '='
                    ];
                }
                
                // Hent alle kursdatoer (filtrert hvis på location page)
                $all_coursedates_popup = get_posts([
                    'post_type' => 'ka_coursedate',
                    'posts_per_page' => -1,
                    'meta_query' => $modal_meta_query,
                ]);
                
                // Samle lokasjonsdata
                $locations_popup = [];
                foreach ($all_coursedates_popup as $coursedate) {
                    $cd_location = get_post_meta($coursedate->ID, 'ka_course_location', true);
                    $cd_freetext = get_post_meta($coursedate->ID, 'ka_course_location_freetext', true);
                    $cd_first_date = get_post_meta($coursedate->ID, 'ka_course_first_date', true);
                    $cd_signup_url = ka_get_coursedate_signup_url($coursedate->ID);
                    
                    if (!empty($cd_location) && !empty($cd_first_date)) {
                        $key = $cd_location;
                        if (!isset($locations_popup[$key])) {
                            $locations_popup[$key] = [
                                'name' => $cd_location,
                                'freetext' => $cd_freetext,
                                'dates' => []
                            ];
                        }
                        $locations_popup[$key]['dates'][] = [
                            'date' => ka_format_date($cd_first_date),
                            'raw_date' => $cd_first_date,
                            'url' => $cd_signup_url
                        ];
                    }
                }
                
                // Sorter datoer innenfor hver lokasjon
                foreach ($locations_popup as &$loc_data) {
                    usort($loc_data['dates'], function($a, $b) {
                        return strcmp($a['raw_date'], $b['raw_date']);
                    });
                }
                unset($loc_data);
                
                // Vis lokasjonene med datoer
                if (!empty($locations_popup)) :
                    foreach ($locations_popup as $loc) : ?>
                        <div class="ka-location-group">
                            <h5><span class="notranslate" translate="no"><?php echo esc_html($loc['name']); ?></span><?php if (!empty($loc['freetext'])) : ?> (<span class="notranslate" translate="no"><?php echo esc_html($loc['freetext']); ?></span>)<?php endif; ?></h5>
                            <ul class="ka-dates-list">
                                <?php foreach ($loc['dates'] as $date_info) : ?>
                                    <li>
                                        <a href="#" class="pameldingskjema" data-url="<?php echo esc_url($date_info['url']); ?>">
                                            <?php echo esc_html($date_info['date']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach;
                else : ?>
                    <p>Ingen kursdatoer tilgjengelig for øyeblikket.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>