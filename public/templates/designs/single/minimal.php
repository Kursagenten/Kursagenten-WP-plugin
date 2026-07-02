<?php
/**
 * Minimal enkeltvisning for kurs
 */

if (!defined('ABSPATH')) exit;

// Hent kurs-ID
$course_id = get_the_ID();

// Hent kursinformasjon
$course_title = get_the_title();
$course_content = get_the_content();
$course_excerpt = get_the_excerpt();
$course_thumbnail = get_the_post_thumbnail_url($course_id, 'large');

// Hovedkurs/underkurs-info for h1 dataattributter (kun underkurs)
$is_parent_course = get_post_meta($course_id, 'ka_is_parent_course', true);
$main_course_title = get_post_meta($course_id, 'ka_main_course_title', true);
$sub_course_location = get_post_meta($course_id, 'ka_sub_course_location', true);

// Hent metadata
$first_course_date = get_post_meta($course_id, 'ka_course_first_date', true);
$last_course_date = get_post_meta($course_id, 'ka_course_last_date', true);
$registration_deadline = get_post_meta($course_id, 'ka_course_registration_deadline', true);
$duration = get_post_meta($course_id, 'ka_course_duration', true);
$coursetime = get_post_meta($course_id, 'ka_course_time', true);
$price = get_post_meta($course_id, 'ka_course_price', true);
$after_price = get_post_meta($course_id, 'ka_course_text_after_price', true);
$location = get_post_meta($course_id, 'ka_course_location', true);
$location_freetext = get_post_meta($course_id, 'ka_course_location_freetext', true);
$location_room = get_post_meta($course_id, 'ka_course_location_room', true);
$button_text = get_post_meta($course_id, 'ka_course_button_text', true);
$show_registration_meta = get_post_meta($course_id, 'ka_course_showRegistrationForm', true);
$show_registration = function_exists('kursagenten_normalize_bool')
    ? kursagenten_normalize_bool($show_registration_meta)
    : !empty($show_registration_meta) && $show_registration_meta !== 'false';
$button_label = kursagenten_get_course_button_label((string) $button_text, $show_registration);
$signup_url = get_post_meta($course_id, 'ka_course_signup_url', true);

// Hent taksonomier
$instructors = get_the_terms($course_id, 'ka_instructors');
$categories = get_the_terms($course_id, 'ka_coursecategory');
$locations = get_the_terms($course_id, 'ka_course_location');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('kursagenten-single-course minimal-design'); ?>>
    <div class="course-container">
        <header class="course-header">
            <h1 class="course-title"<?php if ($is_parent_course !== 'yes') { echo ' data-course-maintitle="' . esc_attr($main_course_title) . '" data-course-location="' . esc_attr($sub_course_location) . '" data-course-title="' . esc_attr($course_title) . '"'; } ?>><?php echo esc_html($course_title); ?></h1>
            
            <?php if (!empty($course_excerpt)) : ?>
                <div class="course-excerpt">
                    <?php echo wp_kses_post($course_excerpt); ?>
                </div>
            <?php endif; ?>
            
            <div class="course-meta">
                <?php if (!empty($first_course_date)) : ?>
                    <div class="meta-item">
                        <i class="ka-icon icon-calendar-light"></i>
                        <span><?php echo esc_html(ka_format_date($first_course_date)); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($location)) : ?>
                    <div class="meta-item">
                        <i class="ka-icon icon-location-light"></i>
                        <span class="notranslate" translate="no"><?php echo esc_html($location); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($price)) : ?>
                    <div class="meta-item">
                        <i class="ka-icon icon-tag"></i>
                        <span><?php echo esc_html(kursagenten_format_price_display($price)); ?> <?php echo esc_html($after_price); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="course-content-wrapper">
            <div class="course-main-content">
                <?php echo apply_filters('the_content', $course_content); ?>
            </div>
            
            <aside class="course-sidebar">
                <?php if (!empty($course_thumbnail)) : ?>
                    <div class="course-featured-image">
                        <img src="<?php echo esc_url($course_thumbnail); ?>" alt="<?php echo esc_attr($course_title); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="course-info-box">
                    <h3><?php esc_html_e('Kursinformasjon', 'kursagenten'); ?></h3>
                    
                    <div class="info-list">
                        <?php if (!empty($first_course_date)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Startdato:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html(ka_format_date($first_course_date)); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($last_course_date)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Sluttdato:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html(ka_format_date($last_course_date)); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($registration_deadline)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Påmeldingsfrist:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html(ka_format_date($registration_deadline)); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($duration)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Varighet:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html($duration); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($coursetime)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Tidspunkt:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html($coursetime); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($price)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Pris:', 'kursagenten'); ?></div>
                                <div class="info-value"><?php echo esc_html(kursagenten_format_price_display($price)); ?> <?php echo esc_html($after_price); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($location)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Sted:', 'kursagenten'); ?></div>
                                <div class="info-value notranslate" translate="no"><?php echo esc_html($location); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($location_freetext)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Lokasjon:', 'kursagenten'); ?></div>
                                <div class="info-value notranslate" translate="no"><?php echo esc_html($location_freetext); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($location_room)) : ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo esc_html__('Rom:', 'kursagenten'); ?></div>
                                <div class="info-value notranslate" translate="no"><?php echo esc_html($location_room); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($signup_url)) : ?>
                        <div class="sidebar-actions">
                            <a href="<?php echo esc_url($signup_url); ?>" class="course-button primary full-width">
                                <?php echo esc_html($button_label); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($categories)) : ?>
                    <div class="course-categories-box">
                        <h3><?php esc_html_e('Kategorier', 'kursagenten'); ?></h3>
                        <div class="categories-list">
                            <?php foreach ($categories as $category) : ?>
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-tag">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</article>
<style>
/* Minimal design styles */
.kursagenten-single-course.minimal-design {
    --text-color: #333;
    --light-bg: #f8f9fa;
    --border-color: #eee;
    --accent-color: #666;
    --primary-color: #333;
    --border-radius: 0;
    --box-shadow: none;
    --transition: all 0.2s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.course-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.course-header {
    margin-bottom: 3rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 2rem;
}

.course-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin-bottom: 1rem;
}

.course-excerpt {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 1.5rem;
    max-width: 800px;
}

.course-meta {
    display: flex;
    gap: 2rem;
    color: var(--accent-color);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.course-content-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
}

.course-main-content {
    line-height: 1.6;
}

.course-main-content h2 {
    font-weight: 400;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.course-main-content p {
    margin-bottom: 1.5rem;
}

.course-sidebar {
    font-size: 0.95rem;
}

.course-featured-image {
    margin-bottom: 2rem;
}

.course-featured-image img {
    width: 100%;
    height: auto;
    display: block;
}

.course-info-box {
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    padding: 1.5rem;
}

.course-info-box h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-weight: 400;
    font-size: 1.2rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.5rem;
}

.info-list {
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px dotted var(--border-color);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--accent-color);
}

.info-value {
    font-weight: 500;
}

.sidebar-actions {
    margin-top: 1.5rem;
}

.course-button {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    text-align: center;
}

.course-button:hover {
    background: #000;
}

.course-button.full-width {
    display: block;
    width: 100%;
}

.course-categories-box {
    margin-top: 2rem;
}

.course-categories-box h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-weight: 400;
    font-size: 1.2rem;
}

.categories-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.category-tag {
    display: inline-block;
    background: var(--light-bg);
    padding: 0.4rem 0.8rem;
    text-decoration: none;
    color: var(--accent-color);
    transition: var(--transition);
    font-size: 0.9rem;
}

.category-tag:hover {
    background: var(--primary-color);
    color: white;
}

@media (max-width: 768px) {
    .course-content-wrapper {
        grid-template-columns: 1fr;
    }
    
    .course-meta {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .course-title {
        font-size: 2rem;
    }
}
</style> 