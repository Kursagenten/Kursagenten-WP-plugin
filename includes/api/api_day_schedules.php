<?php
/**
 * Day Schedules API
 *
 * Provides on-demand fetching of daySchedules (kursdager) for a single ka_coursedate.
 *
 * Background:
 * The Kursagenten single-course API (`/api/Course/WP/{id}`) returns a `daySchedules`
 * array on each `locations[].schedules[]`. The full course-list API does NOT include
 * this data. Since only a fraction of courses use multi-day schedules with per-day
 * instructors/rooms, we don't store the full payload in wp_postmeta. Instead:
 *
 *   1. During sync we save `ka_course_day_schedules_count` (int) on each coursedate
 *      so list templates know whether/how many days exist without an API call.
 *   2. When the user clicks the "X dager" link, the front-end hits this AJAX
 *      endpoint which fetches the daySchedules from the API on demand.
 *   3. The response is cached in a transient (1 hour) keyed on location+schedule id,
 *      so opening the same popup multiple times only costs one outbound call.
 *
 * The data is for screen display only (popup tables), so it does not need to be
 * indexed or available in REST queries.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Number of seconds to cache the daySchedules response per (location_id, schedule_id).
 *
 * 1 hour balances freshness against load: webhook sync invalidates cache via
 * kursagenten_invalidate_day_schedules_cache() so changes propagate immediately,
 * and otherwise a stale 1-hour view is acceptable for screen display.
 */
if (!defined('KURSAGENTEN_DAY_SCHEDULES_CACHE_TTL')) {
    define('KURSAGENTEN_DAY_SCHEDULES_CACHE_TTL', HOUR_IN_SECONDS);
}

/**
 * Build the transient key for a (location_id, schedule_id) pair.
 *
 * Keys are kept under 172 chars to stay within the wp_options option_name limit.
 */
function kursagenten_day_schedules_instructor_mode() {
    $name_display = function_exists('kursagenten_get_instructor_name_display_setting')
        ? kursagenten_get_instructor_name_display_setting()
        : '';

    if ($name_display === 'firstname' || $name_display === 'lastname') {
        return $name_display;
    }

    return 'full';
}

function kursagenten_day_schedules_transient_key($location_id, $schedule_id) {
    return 'ka_dayschd_' . (int) $location_id . '_' . (int) $schedule_id . '_' . kursagenten_day_schedules_instructor_mode();
}

/**
 * Invalidate cached day schedules for a given location+schedule.
 *
 * Called from the course-sync flow whenever a schedule is updated so the popup
 * always reflects the latest sync state.
 *
 * @param int $location_id Kursagenten location/courseId.
 * @param int $schedule_id Kursagenten schedule id.
 */
function kursagenten_invalidate_day_schedules_cache($location_id, $schedule_id) {
    if ((int) $location_id <= 0 || (int) $schedule_id <= 0) {
        return;
    }

    // Remove all instructor name-display variants for this schedule.
    $base = 'ka_dayschd_' . (int) $location_id . '_' . (int) $schedule_id . '_';
    delete_transient($base . 'full');
    delete_transient($base . 'firstname');
    delete_transient($base . 'lastname');
}

/**
 * Format a "HHMM" string (e.g. "0800") to "HH:MM".
 *
 * Falls back gracefully if the input is already formatted or malformed.
 */
function kursagenten_format_day_schedule_time($time_value) {
    if (!is_string($time_value) && !is_int($time_value)) {
        return '';
    }

    $time_value = (string) $time_value;
    $digits = preg_replace('/\D+/', '', $time_value);

    if ($digits === '' || strlen($digits) > 4) {
        return $time_value; // Leave as-is; admin entered something custom.
    }

    $digits = str_pad($digits, 4, '0', STR_PAD_LEFT);
    return substr($digits, 0, 2) . ':' . substr($digits, 2, 2);
}

/**
 * Format start/end into "HH:MM - HH:MM" or just one side if the other is empty.
 */
function kursagenten_format_day_schedule_timespan($start_time, $end_time) {
    $start = kursagenten_format_day_schedule_time($start_time);
    $end = kursagenten_format_day_schedule_time($end_time);

    if ($start !== '' && $end !== '') {
        return $start . ' - ' . $end;
    }
    return $start !== '' ? $start : $end;
}

/**
 * Resolve instructor name according to Kursdesign -> Instruktører -> Navnevisning.
 *
 * Uses the same option key as taxonomy pages. If the instructor term exists, we
 * reuse term meta (first/last name) when relevant. If not, we fall back to a
 * simple split of the incoming full name so popup data still follows the setting.
 *
 * @param string $instructor_name Raw instructor name from API payload.
 * @return string
 */
function kursagenten_format_day_schedule_instructor_name($instructor_name) {
    if (!is_string($instructor_name) || $instructor_name === '') {
        return '';
    }

    $instructor_name = sanitize_text_field($instructor_name);
    if ($instructor_name === '') {
        return '';
    }

    $name_display = function_exists('kursagenten_get_instructor_name_display_setting')
        ? kursagenten_get_instructor_name_display_setting()
        : '';

    // Only apply transformation for explicit first/last name modes.
    if ($name_display !== 'firstname' && $name_display !== 'lastname') {
        return $instructor_name;
    }

    static $display_name_cache = [];
    $normalized_name = function_exists('mb_strtolower')
        ? mb_strtolower($instructor_name)
        : strtolower($instructor_name);
    $cache_key = $name_display . '|' . $normalized_name;
    if (isset($display_name_cache[$cache_key])) {
        return $display_name_cache[$cache_key];
    }

    $display_name = '';
    $term = get_term_by('name', $instructor_name, 'ka_instructors');
    if ($term instanceof WP_Term) {
        if (function_exists('get_instructor_display_name')) {
            $display_name = (string) get_instructor_display_name($term);
        } else {
            $meta_key = $name_display === 'firstname' ? 'instructor_firstname' : 'instructor_lastname';
            $display_name = (string) get_term_meta($term->term_id, $meta_key, true);
        }
    }

    // Fallback when term is missing or term meta is empty.
    if ($display_name === '') {
        $parts = preg_split('/\s+/u', trim($instructor_name)) ?: [];
        if ($name_display === 'firstname') {
            $display_name = (string) ($parts[0] ?? $instructor_name);
        } else {
            $display_name = !empty($parts) ? (string) $parts[count($parts) - 1] : $instructor_name;
        }
    }

    $display_name = sanitize_text_field($display_name);
    if ($display_name === '') {
        $display_name = $instructor_name;
    }

    $display_name_cache[$cache_key] = $display_name;
    return $display_name;
}

/**
 * Format a daySchedule item from the Kursagenten API into the shape consumed by the front-end.
 *
 * @param array $day_schedule Raw item from `daySchedules` in the single-course API.
 * @return array{
 *     date_raw: string,
 *     date_formatted: string,
 *     weekday: string,
 *     time_formatted: string,
 *     start_time: string,
 *     end_time: string,
 *     instructors: array<int, string>,
 *     rooms: array<int, string>
 * }
 */
function kursagenten_prepare_day_schedule_item(array $day_schedule) {
    $date_raw = isset($day_schedule['date']) ? (string) $day_schedule['date'] : '';

    $date_formatted = '';
    $weekday = '';
    if ($date_raw !== '') {
        $timestamp = strtotime($date_raw);
        if ($timestamp !== false) {
            $date_formatted = ka_format_date($date_raw);
            // wp_date() honors site locale, so weekday is localized ("Torsdag" etc).
            $weekday = wp_date('l', $timestamp);
            // Ensure first letter is capitalized for consistent display in Norwegian.
            if (function_exists('mb_strtoupper') && $weekday !== '') {
                $weekday = mb_strtoupper(mb_substr($weekday, 0, 1)) . mb_substr($weekday, 1);
            }
        }
    }

    $instructors = [];
    if (!empty($day_schedule['instructors']) && is_array($day_schedule['instructors'])) {
        foreach ($day_schedule['instructors'] as $instructor) {
            if (is_string($instructor) && $instructor !== '') {
                $formatted_instructor = kursagenten_format_day_schedule_instructor_name($instructor);
                if ($formatted_instructor !== '') {
                    $instructors[] = $formatted_instructor;
                }
            }
        }
    }

    $rooms = [];
    if (!empty($day_schedule['locationRooms']) && is_array($day_schedule['locationRooms'])) {
        foreach ($day_schedule['locationRooms'] as $room) {
            if (is_string($room) && $room !== '') {
                $rooms[] = sanitize_text_field($room);
            }
        }
    }

    return [
        'date_raw'       => $date_raw,
        'date_formatted' => $date_formatted,
        'weekday'        => $weekday,
        'time_formatted' => kursagenten_format_day_schedule_timespan(
            $day_schedule['startTime'] ?? '',
            $day_schedule['endTime'] ?? ''
        ),
        'start_time'     => isset($day_schedule['startTime']) ? (string) $day_schedule['startTime'] : '',
        'end_time'       => isset($day_schedule['endTime']) ? (string) $day_schedule['endTime'] : '',
        'instructors'    => $instructors,
        'rooms'          => $rooms,
    ];
}

/**
 * Fetch and prepare daySchedules for a (location_id, schedule_id) pair.
 *
 * Uses a transient cache to avoid hitting the Kursagenten API repeatedly.
 *
 * @param int $location_id   Kursagenten location/courseId.
 * @param int $schedule_id   Kursagenten schedule id.
 * @param int $main_course_id Used to call the single-course endpoint. Falls back
 *                           to $location_id when empty.
 * @param bool $force_refresh Bypass cache when true.
 * @return array{
 *     status: string,
 *     days: array<int, array<string, mixed>>,
 *     count: int,
 *     fetched_at: int
 * }|false False on unrecoverable error.
 */
function kursagenten_get_day_schedules($location_id, $schedule_id, $main_course_id = 0, $force_refresh = false) {
    $location_id = (int) $location_id;
    $schedule_id = (int) $schedule_id;
    $main_course_id = (int) $main_course_id;

    if ($location_id <= 0 || $schedule_id <= 0) {
        return false;
    }

    $transient_key = kursagenten_day_schedules_transient_key($location_id, $schedule_id);

    if (!$force_refresh) {
        $cached = get_transient($transient_key);
        if (is_array($cached) && isset($cached['days'])) {
            return $cached;
        }
    }

    // Prefer fetching by main_course_id since that endpoint returns the full course
    // tree. Fall back to location_id (the API also accepts location ids on the
    // single-course endpoint and returns the same shape).
    $api_id = $main_course_id > 0 ? $main_course_id : $location_id;

    $course_details = kursagenten_get_course_details($api_id);
    if (empty($course_details) || empty($course_details['locations'])) {
        return false;
    }

    $matched_schedule = null;
    foreach ($course_details['locations'] as $location_data) {
        if ((int) ($location_data['courseId'] ?? 0) !== $location_id) {
            continue;
        }
        if (empty($location_data['schedules']) || !is_array($location_data['schedules'])) {
            continue;
        }
        foreach ($location_data['schedules'] as $schedule_data) {
            if ((int) ($schedule_data['id'] ?? 0) === $schedule_id) {
                $matched_schedule = $schedule_data;
                break 2;
            }
        }
    }

    if ($matched_schedule === null) {
        // Schedule no longer exists in the API – store an empty result so we don't
        // re-query on every click for a deleted schedule, but use a shorter TTL.
        $empty_payload = [
            'status'     => 'not_found',
            'days'       => [],
            'count'      => 0,
            'fetched_at' => time(),
        ];
        set_transient($transient_key, $empty_payload, MINUTE_IN_SECONDS * 15);
        return $empty_payload;
    }

    $day_items = [];
    if (!empty($matched_schedule['daySchedules']) && is_array($matched_schedule['daySchedules'])) {
        foreach ($matched_schedule['daySchedules'] as $raw_item) {
            if (is_array($raw_item)) {
                $day_items[] = kursagenten_prepare_day_schedule_item($raw_item);
            }
        }

        // Sort by date ascending to guarantee chronological order even if the API changes.
        usort($day_items, function ($a, $b) {
            return strcmp((string) ($a['date_raw'] ?? ''), (string) ($b['date_raw'] ?? ''));
        });
    }

    $payload = [
        'status'     => 'ok',
        'days'       => $day_items,
        'count'      => count($day_items),
        'fetched_at' => time(),
    ];

    set_transient($transient_key, $payload, KURSAGENTEN_DAY_SCHEDULES_CACHE_TTL);

    return $payload;
}

/**
 * Enqueue + localize the day-schedules front-end assets.
 *
 * Safe to call multiple times: uses wp_script_is() guards so additional callers
 * (e.g. course-list-shortcode, Gutenberg block render) don't trigger duplicate
 * registration. Returns early in admin/REST/AJAX contexts where the assets are
 * meaningless and would otherwise pollute global state.
 */
function kursagenten_enqueue_day_schedules_assets() {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $assets_version = function_exists('kursagenten_get_design_assets_version')
        ? kursagenten_get_design_assets_version()
        : (defined('KURSAG_VERSION') ? KURSAG_VERSION : false);

    if (!wp_script_is('kursagenten-day-schedules', 'enqueued')) {
        wp_enqueue_script(
            'kursagenten-day-schedules',
            KURSAG_PLUGIN_URL . '/assets/js/public/course-day-schedules.js',
            array('jquery'),
            $assets_version,
            true
        );

        wp_localize_script(
            'kursagenten-day-schedules',
            'kursagentenDaySchedules',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('kursagenten_day_schedules'),
                'i18n'    => array(
                    'close'          => __('Lukk', 'kursagenten'),
                    'loading'        => __('Laster kursdager…', 'kursagenten'),
                    'empty'          => __('Ingen kursdager tilgjengelig.', 'kursagenten'),
                    'errorGeneric'   => __('Kunne ikke hente kursdager.', 'kursagenten'),
                    'errorNetwork'   => __('Nettverksfeil. Prøv igjen.', 'kursagenten'),
                    'errorConfig'    => __('AJAX-konfigurasjon mangler.', 'kursagenten'),
                    'colDate'        => __('Dato', 'kursagenten'),
                    'colWeekday'     => __('Dag', 'kursagenten'),
                    'colTime'        => __('Klokkeslett', 'kursagenten'),
                    'colInstructors' => __('Instruktør', 'kursagenten'),
                    'colRoom'        => __('Lokale', 'kursagenten'),
                ),
            )
        );
    }

    // Ensure the shared modal CSS (frontend-course-style.css) is present.
    // The course-list shortcode enqueues this stylesheet too, but on plain
    // archive/single pages it is loaded via the styles hook. We don't enqueue
    // it again here to avoid ordering issues – CSS for the modal lives in the
    // main stylesheet which is already loaded wherever this script runs.
}

/**
 * Locate a ka_coursedate post by Kursagenten (location_id, schedule_id) pair.
 *
 * @return int Post ID, or 0 when no matching coursedate exists.
 */
function kursagenten_find_coursedate_by_kursagenten_ids($location_id, $schedule_id) {
    $location_id = (int) $location_id;
    $schedule_id = (int) $schedule_id;

    if ($location_id <= 0 || $schedule_id <= 0) {
        return 0;
    }

    $posts = get_posts([
        'post_type'        => 'ka_coursedate',
        'post_status'      => ['publish', 'draft'],
        'posts_per_page'   => 1,
        'fields'           => 'ids',
        'no_found_rows'    => true,
        'suppress_filters' => false,
        'meta_query'       => [
            'relation' => 'AND',
            ['key' => 'ka_location_id', 'value' => $location_id],
            ['key' => 'ka_schedule_id', 'value' => $schedule_id],
        ],
    ]);

    return !empty($posts) ? (int) $posts[0] : 0;
}

/**
 * AJAX handler: kursagenten_get_day_schedules
 *
 * Request (any of these forms works):
 *   - coursedate_id (int): WordPress post ID of a ka_coursedate, OR
 *   - location_id + schedule_id (int): Kursagenten location and schedule IDs.
 *   - nonce         (string, required): nonce created with kursagenten_day_schedules.
 *   - refresh       (1, optional): bypass cache (admin users only).
 *
 * Response (JSON):
 *   {
 *     success: true,
 *     data: {
 *       coursedate_id: int (0 if not stored locally),
 *       course_title: string,
 *       days: [ { date_formatted, weekday, time_formatted, instructors, rooms }, ... ],
 *       count: int,
 *       fetched_at: int  // unix timestamp
 *     }
 *   }
 */
function kursagenten_ajax_get_day_schedules() {
    check_ajax_referer('kursagenten_day_schedules', 'nonce');

    $coursedate_id = isset($_POST['coursedate_id']) ? (int) $_POST['coursedate_id'] : 0;
    $location_id   = isset($_POST['location_id']) ? (int) $_POST['location_id'] : 0;
    $schedule_id   = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : 0;
    $main_course_id = 0;
    $course_title  = '';

    if ($coursedate_id > 0) {
        // Look up Kursagenten IDs from a WP post.
        $post = get_post($coursedate_id);
        if (!$post || $post->post_type !== 'ka_coursedate') {
            wp_send_json_error([
                'code'    => 'not_found',
                'message' => __('Kursdato finnes ikke. coursedate_id må være WordPress post-ID for en ka_coursedate, ikke Kursagentens kurs-ID.', 'kursagenten'),
            ], 404);
        }
        $location_id    = (int) get_post_meta($coursedate_id, 'ka_location_id', true);
        $schedule_id    = (int) get_post_meta($coursedate_id, 'ka_schedule_id', true);
        $main_course_id = (int) get_post_meta($coursedate_id, 'ka_main_course_id', true);
        $course_title   = (string) get_post_meta($coursedate_id, 'ka_course_title', true);
        if ($course_title === '') {
            $course_title = (string) get_the_title($coursedate_id);
        }
    } elseif ($location_id > 0 && $schedule_id > 0) {
        // Direct Kursagenten lookup. Try to also resolve a matching WP post so the
        // count meta stays in sync, but the call still works if none exists.
        $coursedate_id = kursagenten_find_coursedate_by_kursagenten_ids($location_id, $schedule_id);
        if ($coursedate_id > 0) {
            $main_course_id = (int) get_post_meta($coursedate_id, 'ka_main_course_id', true);
            $course_title   = (string) get_post_meta($coursedate_id, 'ka_course_title', true);
            if ($course_title === '') {
                $course_title = (string) get_the_title($coursedate_id);
            }
        }
    } else {
        wp_send_json_error([
            'code'    => 'invalid_params',
            'message' => __('Oppgi enten coursedate_id (WP post-ID) eller location_id + schedule_id (Kursagenten-IDer).', 'kursagenten'),
        ], 400);
    }

    if ($location_id <= 0 || $schedule_id <= 0) {
        wp_send_json_error([
            'code'    => 'missing_ids',
            'message' => __('Mangler Kursagenten-ID-er på kursdato.', 'kursagenten'),
        ], 400);
    }

    $force_refresh = !empty($_POST['refresh']) && current_user_can('manage_options');

    $result = kursagenten_get_day_schedules($location_id, $schedule_id, $main_course_id, $force_refresh);
    if ($result === false) {
        wp_send_json_error([
            'code'    => 'api_error',
            'message' => __('Klarte ikke å hente kursdager fra Kursagenten.', 'kursagenten'),
        ], 502);
    }

    // Self-healing: keep the cached count in post meta in sync with what the API
    // actually returned. Avoids stale "X dager" labels after a webhook race.
    if ($coursedate_id > 0) {
        $stored_count = get_post_meta($coursedate_id, 'ka_course_day_schedules_count', true);
        if ((string) $stored_count !== (string) $result['count']) {
            update_post_meta($coursedate_id, 'ka_course_day_schedules_count', (int) $result['count']);
        }
    }

    wp_send_json_success([
        'coursedate_id' => $coursedate_id,
        'location_id'   => $location_id,
        'schedule_id'   => $schedule_id,
        'course_title'  => $course_title,
        'days'          => $result['days'],
        'count'         => (int) $result['count'],
        'fetched_at'    => (int) $result['fetched_at'],
        'status'        => (string) ($result['status'] ?? 'ok'),
    ]);
}
add_action('wp_ajax_kursagenten_get_day_schedules', 'kursagenten_ajax_get_day_schedules');
add_action('wp_ajax_nopriv_kursagenten_get_day_schedules', 'kursagenten_ajax_get_day_schedules');
