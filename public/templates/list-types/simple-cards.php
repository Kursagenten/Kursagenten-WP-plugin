<?php
/**
 * Simple cards list type - Enkle kort
 * Displays courses as simple cards with title, excerpt, duration, and next course date
 */

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

// Check view type from args - simple-cards always uses main_courses
$view_type = 'main_courses';
$is_taxonomy_page = isset($args['is_taxonomy_page']) && $args['is_taxonomy_page'];

// When view_type is 'main_courses', the query returns ka_coursedate posts
// We need to find the main course based on the coursedate
$coursedate_id = get_the_ID();

// Get main_course_id from coursedate
$main_course_id = get_post_meta($coursedate_id, 'ka_main_course_id', true);

// Grouping mode (deduplication itself is handled in get_course_template_part):
//  - 'course'          => one card per course, based on the parent (main) course.
//  - 'course_location' => one card per course + location, based on the subcourse
//                         for that location, with the next date for that location.
$simple_cards_grouping = kursagenten_normalize_simple_cards_grouping($args['simple_cards_grouping'] ?? '');

// Location of the current (nearest) coursedate, used for course_location grouping.
// The query is sorted by ascending start date, so the first coursedate per group
// is the nearest one for that course/location.
$current_location_id = (string) get_post_meta($coursedate_id, 'ka_location_id', true);
$has_location_id = ($current_location_id !== '' && $current_location_id !== '0');

// Check if we need to filter by location (taxonomy page)
$taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : null;
$current_term = isset($args['current_term']) ? $args['current_term'] : null;

// Resolve the course post used as the card base.
$course_id = 0;

if ($simple_cards_grouping === 'course_location' && $has_location_id) {
    // Use the subcourse tied to this location.
    $sub_courses = get_posts([
        'post_type' => 'ka_course',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'ka_main_course_id', 'value' => $main_course_id, 'compare' => '='],
            ['key' => 'ka_location_id', 'value' => $current_location_id, 'compare' => '='],
            ['key' => 'ka_is_parent_course', 'compare' => 'NOT EXISTS'],
        ],
    ]);
    if (!empty($sub_courses)) {
        $course_id = $sub_courses[0]->ID;
    }
}

if (!$course_id) {
    // Default (course grouping) or fallback: use the parent (main) course.
    $main_courses = get_posts([
        'post_type' => 'ka_course',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'ka_main_course_id', 'value' => $main_course_id, 'compare' => '='],
            ['key' => 'ka_is_parent_course', 'value' => 'yes', 'compare' => '='],
        ],
    ]);
    if (!empty($main_courses)) {
        $course_id = $main_courses[0]->ID;
    }
}

if ($course_id) {
    if ($simple_cards_grouping === 'course_location') {
        // Use the clean main course title (without location suffix); location is
        // shown separately via the "Sted" field.
        $main_course_title_meta = (string) get_post_meta($course_id, 'ka_main_course_title', true);
        $course_title = $main_course_title_meta !== '' ? $main_course_title_meta : get_the_title($course_id);
    } else {
        $course_title = get_the_title($course_id);
    }
    $excerpt = get_the_excerpt($course_id);
} else {
    // Fallback if no course post exists
    $course_id = 0;
    $course_title = '';
    $excerpt = '';
}

// Build meta query for coursedates (used to find the next available date).
$meta_query = [
    'relation' => 'AND',
    ['key' => 'ka_main_course_id', 'value' => $main_course_id],
];

if ($simple_cards_grouping === 'course_location' && $has_location_id) {
    // Restrict to the same location so the next date matches that location.
    $meta_query[] = [
        'key' => 'ka_location_id',
        'value' => $current_location_id,
        'compare' => '=',
    ];
} elseif ($taxonomy === 'ka_course_location' && $current_term) {
    // If on a location taxonomy page, filter coursedates by that location
    $meta_query[] = [
        'key' => 'ka_course_location',
        'value' => $current_term->name,
        'compare' => '=',
    ];
}

$related_coursedates = get_posts([
    'post_type' => 'ka_coursedate',
    'posts_per_page' => -1,
    'meta_query' => $meta_query,
]);

// Convert to array of IDs
$related_coursedate_ids = array_map(function($post) {
    return $post->ID;
}, $related_coursedates);

// Get data from first available coursedate
$selected_coursedate_data = get_selected_coursedate_data($related_coursedate_ids);

// Get location information from selected coursedate
$location = $selected_coursedate_data['location'] ?? '';
$location_freetext = $selected_coursedate_data['location_freetext'] ?? '';

// Get placeholder image from settings
$options = get_option('design_option_name');
$placeholder_image = !empty($options['ka_plassholderbilde_kurs']) 
    ? $options['ka_plassholderbilde_kurs']
    : rtrim(KURSAG_PLUGIN_URL, '/') . '/assets/images/placeholder-kurs.jpg';

// Get image
$featured_image_thumb = $course_id ? get_the_post_thumbnail_url($course_id, 'medium') : '';
$featured_image_thumb = $featured_image_thumb ?: $placeholder_image;

// Set up link to course. Course grouping links to the parent course; course+location
// grouping links to the subcourse for that location (resolved in $course_id above).
$course_link = $course_id ? get_permalink($course_id) : '#';

// Get data from first available coursedate
$first_course_date = $selected_coursedate_data['first_date'] ?? '';
$last_course_date = $selected_coursedate_data['last_date'] ?? '';
$first_course_date_raw = $selected_coursedate_data['first_date_raw'] ?? '';
$last_course_date_raw = $selected_coursedate_data['last_date_raw'] ?? '';
$registration_deadline = $selected_coursedate_data['registration_deadline'] ?? '';
$coursetime = $selected_coursedate_data['time'] ?? '';
$duration = $selected_coursedate_data['duration'] ?? '';
$price = $selected_coursedate_data['price'] ?? '';
$after_price = $selected_coursedate_data['after_price'] ?? '';
$location_room = $selected_coursedate_data['course_location_room'] ?? '';
$day_schedules_count = (int) ($selected_coursedate_data['day_schedules_count'] ?? 0);
$day_schedules_coursedate_id = (int) ($selected_coursedate_data['id'] ?? 0);

$course_count = $course_count ?? 0;
$item_class = $course_count === 1 ? ' single-item' : '';
$list_display = kursagenten_get_list_display_fields($args);
$instructor_links = kursagenten_get_course_instructor_links((int) $course_id);

// Check if images should be shown
// Priority: shortcode attribute > taxonomy-specific setting > global setting
$shortcode_show_images = isset($args['shortcode_show_images']) ? $args['shortcode_show_images'] : null;

// If shortcode explicitly sets bilder parameter to 'yes' or 'no', use it
if ($shortcode_show_images === 'yes' || $shortcode_show_images === 'no') {
    // Use shortcode attribute if explicitly set to yes or no
    $show_images = $shortcode_show_images;
} elseif ($is_taxonomy_page) {
    // Taxonomy page: use taxonomy settings with proper override handling
    $taxonomy = get_queried_object()->taxonomy;
    $show_images = get_taxonomy_setting($taxonomy, 'show_images', 'yes');
} else {
    // Standard: use global setting
    $show_images = get_option('kursagenten_show_images', 'yes');
}

// Get course categories for data-category attribute
$course_categories = get_the_terms($course_id, 'ka_coursecategory');
$category_slugs = [];
if (!empty($course_categories) && !is_wp_error($course_categories)) {
    foreach ($course_categories as $category) {
        $category_slugs[] = $category->slug;
    }
}
$category_slugs = array_unique($category_slugs);

// Generate view type class
$view_type_class = ' view-type-maincourses';
?>

<div class="courselist-item simple-card-item<?php echo $item_class . $view_type_class; ?>" data-location="<?php echo esc_attr($location_freetext); ?>" data-category="<?php echo esc_attr(implode(' ', $category_slugs)); ?>">
        <div class="simple-card<?php echo ($show_images === 'yes') ? ' with-image' : ''; ?>">
            <?php // Stretched link overlay: covers the whole card so it is clickable,
                  // while keeping nested interactive elements (instructor links, the
                  // day-schedules button) as valid, clickable siblings. Wrapping the
                  // whole card in an <a> would nest <a>/<button> inside <a> (invalid
                  // HTML), which browsers "repair" by splitting the anchor and breaking
                  // the layout. ?>
            <a href="<?php echo esc_url($course_link); ?>" class="simple-card-link" title="<?php echo esc_attr($course_title); ?>" aria-label="Se kurs: <?php echo esc_attr($course_title); ?>"></a>
            <?php if ($show_images === 'yes') : ?>
            <!-- Image area - left side with same border-radius -->
            <div class="simple-card-image">
                <img src="<?php echo esc_url($featured_image_thumb); ?>" 
                     alt="<?php echo esc_attr($course_title); ?>" 
                     title="<?php echo esc_attr($course_title); ?>">
            </div>
            <?php endif; ?>
            
            <!-- Content area -->
            <div class="simple-card-content">
                <!-- Title -->
                <h3 class="simple-card-title">
                    <?php echo esc_html($course_title); ?>
                </h3>
                
                <!-- Excerpt -->
                <?php if (!empty($excerpt)) : ?>
                <div class="simple-card-excerpt">
                    <?php echo wp_trim_words(wp_kses_post($excerpt), 60, '...'); ?>
                </div>
                <?php endif; ?>
                
                <!-- Meta row: optional fields + next course date -->
                <div class="simple-card-meta">
                    <?php
                    $list_meta_tooltips = kursagenten_get_list_meta_tooltips();
                    $list_date_text = kursagenten_list_format_course_dates(
                        $first_course_date,
                        $last_course_date ?? '',
                        !empty($list_display['last_date']),
                        $first_course_date_raw ?? '',
                        $last_course_date_raw ?? ''
                    );
                    $is_location_taxonomy = ($taxonomy === 'ka_course_location');
                    $show_location_name = !empty($list_display['location']) && !$is_location_taxonomy && !empty($location);
                    $show_location_freetext = !empty($list_display['location_freetext']) && !empty($location_freetext);
                    $show_location_in_date = $is_location_taxonomy
                        ? $show_location_freetext
                        : ($show_location_name || $show_location_freetext);
                    ?>
                    <?php if ($list_date_text !== '') : ?>
                    <span class="simple-card-date">
                        <i class="ka-icon icon-calendar"></i>
                        <span>
                            Neste kurs: <?php echo esc_html($list_date_text); ?>
                            <?php if ($show_location_in_date) : ?>
                                &nbsp;-&nbsp;<?php if ($show_location_name) : ?><span class="notranslate" translate="no"><?php echo esc_html($location); ?></span><?php endif; ?><?php if ($show_location_freetext) : ?><?php if ($is_location_taxonomy) : ?><span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span><?php else : ?><?php if ($show_location_name) : ?>&nbsp;<?php endif; ?>(<span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span>)<?php endif; ?><?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <?php else :
                        // No course date available. When grouping by course + location,
                        // show the location in the slot where "Neste kurs" normally
                        // appears, so the card still carries its location context.
                        // Mirror the date-row logic so freetext location is included
                        // too (otherwise two cards for the same place but different
                        // freetext would look identical). Reuses .simple-card-date for
                        // layout/color.
                        $show_fallback_location = ($simple_cards_grouping === 'course_location')
                            && ($show_location_name || $show_location_freetext);
                        if ($show_fallback_location) : ?>
                    <span class="simple-card-date simple-card-location-fallback">
                        <i class="ka-icon icon-location"></i>
                        <span>
                            <?php if ($show_location_name) : ?><span class="notranslate" translate="no"><?php echo esc_html($location); ?></span><?php endif; ?><?php if ($show_location_freetext) : ?><?php if ($is_location_taxonomy) : ?><span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span><?php else : ?><?php if ($show_location_name) : ?>&nbsp;<?php endif; ?>(<span class="notranslate" translate="no"><?php echo esc_html($location_freetext); ?></span>)<?php endif; ?><?php endif; ?>
                        </span>
                    </span>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($list_display['registration_deadline'] && !empty($registration_deadline)) : ?>
                    <span class="simple-card-registration-deadline ka-cursor-tooltip ka-cursor-tooltip--help" data-title="<?php echo esc_attr($list_meta_tooltips['registration_deadline']); ?>">
                        <i class="ka-icon icon-alarmclock"></i>
                        <span><?php echo esc_html($registration_deadline); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if ($list_display['time'] && !empty($coursetime)) : ?>
                    <span class="simple-card-time">
                        <i class="ka-icon icon-time"></i>
                        <span><?php echo esc_html($coursetime); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if ($list_display['duration'] && !empty($duration)) : ?>
                    <span class="simple-card-duration">
                        <i class="ka-icon icon-stopwatch"></i>
                        <span><?php echo esc_html($duration); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($list_display['day_schedules']) && $day_schedules_count >= 2 && $day_schedules_coursedate_id > 0) : ?>
                    <span class="simple-card-day-schedules"><?php
                        // Nested inside the wrapping <a class="simple-card-link">.
                        echo kursagenten_render_day_schedules_link(
                            $day_schedules_coursedate_id,
                            $day_schedules_count,
                            $course_title,
                            [
                                'icon'           => 'icon-calendar',
                                'tag'            => 'button',
                                'cursor_tooltip' => $list_meta_tooltips['day_schedules'],
                            ]
                        );
                    ?></span>
                    <?php endif; ?>

                    <?php if ($list_display['room'] && !empty($location_room)) : ?>
                    <span class="simple-card-room notranslate ka-cursor-tooltip ka-cursor-tooltip--help" translate="no" data-title="<?php echo esc_attr($list_meta_tooltips['room']); ?>">
                        <i class="ka-icon icon-grid"></i>
                        <span><?php echo esc_html($location_room); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if ($list_display['instructor'] && !empty($instructor_links)) : ?>
                    <span class="simple-card-instructors">
                        <i class="ka-icon icon-user"></i>
                        <span><?php echo implode(', ', $instructor_links); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if ($list_display['price'] && !empty($price)) : ?>
                    <span class="simple-card-price">
                        <i class="ka-icon icon-layers"></i>
                        <span><?php echo esc_html($price); ?> <?php echo isset($after_price) ? esc_html($after_price) : ''; ?></span>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Arrow indicator - right side -->
            <div class="simple-card-arrow">
                <i class="ka-icon icon-arrow-right-short"></i>
            </div>
        </div>
</div>
