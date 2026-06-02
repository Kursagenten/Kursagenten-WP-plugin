<?php
/**
 * Full-bredde layout-wrapper
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<div id="ka" class="kursagenten-wrapper ka-full-width<?php echo esc_attr(ka_get_filter_sidebar_box_wrapper_class()); ?>">
    <main id="ka-main" class="kursagenten-main" role="main">
        <div class="ka-container">
            <?php
            // Last inn riktig design-template basert på kontekst
            kursagenten_get_design_template();
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>