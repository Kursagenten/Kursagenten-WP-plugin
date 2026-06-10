<?php
if (!defined('ABSPATH')) {
    exit;
}

// Hent bedriftsvariabler for tilpassede URL-slugs
$url_options = get_option('kag_seo_option_name');
$kurskategori = !empty($url_options['ka_url_rewrite_kurskategori']) ? $url_options['ka_url_rewrite_kurskategori'] : 'kurskategori';
$kurssted = !empty($url_options['ka_url_rewrite_kurssted']) ? $url_options['ka_url_rewrite_kurssted'] : 'kurssted';
$instruktor = !empty($url_options['ka_url_rewrite_instruktor']) ? $url_options['ka_url_rewrite_instruktor'] : 'instruktorer';

function capitalize_first_letter($string) {
    return ucfirst($string);
}

// Registrering av taksonomien 'kurskategori'
register_taxonomy('ka_coursecategory', array('ka_course', 'ka_coursedate', 'instructor'), array(
    'labels' => array(
        'name' => __('Kurskategorier', 'kursagenten'),
        'singular_name' => capitalize_first_letter($kurskategori),
        'menu_name' => __('Kurskategorier', 'kursagenten'),
        'all_items' => __('Alle kurskategorier', 'kursagenten'),
        'edit_item' => __('Rediger kurskategori', 'kursagenten'),
        'view_item' => __('Vis kurskategori', 'kursagenten'),
        'update_item' => __('Oppdater kurskategori', 'kursagenten'),
        'add_new_item' => __('Legg til kurskategori', 'kursagenten'),
        'new_item_name' => __('Nytt navn for kurskategori', 'kursagenten'),
        'parent_item' => __('Foreldrekategori', 'kursagenten'),
        'parent_item_colon' => __('Foreldrekategori:', 'kursagenten'),
        'search_items' => __('Søk etter kurskategori', 'kursagenten'),
        'most_used' => __('Mest brukt', 'kursagenten'),
        'not_found' => __('Ingen kurskategorier funnet', 'kursagenten'),
        'no_terms' => __('Ingen kurskategorier', 'kursagenten'),
        'name_field_description' => __('Navnet er det som vises på siden', 'kursagenten'),
        'slug_field_description' => __('"Slug" er den SEO-vennlige versjonen av url-en. Eksempel /mitt-kurs', 'kursagenten'),
        'parent_field_description' => __('Velg en forelder for å lage et hierarki, og la dette bli en subkategori.', 'kursagenten'),
        'desc_field_description' => __('Kort beskrivelse brukes i oversikter og som innledende tekst på detaljside', 'kursagenten'),
        'filter_by_item' => __('Filtrer på kurskategori', 'kursagenten'),
        'items_list_navigation' => __('Kurskategorier listenavigasjon', 'kursagenten'),
        'items_list' => __('Kurskategorier liste', 'kursagenten'),
        'back_to_items' => __('← Tilbake til kurskategorier', 'kursagenten'),
        'item_link' => __('Kurskategori link', 'kursagenten'),
        'item_link_description' => __('Link til en kurskategori', 'kursagenten'),
        'archives'  => capitalize_first_letter($kurskategori),
    ),
    'public' => true,
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_menu' => false,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'rewrite' => array(
        'slug' => $kurskategori,
    ),
));

// Registrering av taksonomien 'kurssted'
register_taxonomy('ka_course_location', array('ka_course', 'ka_coursedate', 'instructor'), array(
    'labels' => array(
        'name' => __('Kurssteder', 'kursagenten'),
        'singular_name' => capitalize_first_letter($kurssted),
        'menu_name' => __('Kurssteder', 'kursagenten'),
        'all_items' => __('Alle kurssteder', 'kursagenten'),
        'edit_item' => __('Rediger kurssted', 'kursagenten'),
        'view_item' => __('Vis kurssted', 'kursagenten'),
        'update_item' => __('Oppdater kurssted', 'kursagenten'),
        'add_new_item' => __('Legg til kurssted', 'kursagenten'),
        'new_item_name' => __('Navn på nytt kurssted', 'kursagenten'),
        'parent_item' => __('Overordnet kurssted', 'kursagenten'),
        'parent_item_colon' => __('Overordnet kurssted:', 'kursagenten'),
        'search_items' => __('Søk i kurssteder', 'kursagenten'),
        'most_used' => __('Mest brukt', 'kursagenten'),
        'not_found' => __('Ingen kurssteder funnet', 'kursagenten'),
        'no_terms' => __('Ingen kurssteder', 'kursagenten'),
        'filter_by_item' => __('Filtrer på kurssted', 'kursagenten'),
        'items_list_navigation' => __('Kurssteder listenavigasjon', 'kursagenten'),
        'items_list' => __('Kurssteder liste', 'kursagenten'),
        'back_to_items' => __('← Tilbake til kurssteder', 'kursagenten'),
        'item_link' => __('Kurssted link', 'kursagenten'),
        'item_link_description' => __('Link til et kurssted', 'kursagenten'),
        'archives'  => capitalize_first_letter($kurssted),
        'name_field_description' => wp_kses_post(sprintf(
            __('Navnet slik det som vises på siden. Kan endres under <a href="%s">Synkronisering</a>.', 'kursagenten'),
            esc_url(admin_url('admin.php?page=kursinnstillinger#places'))
        )),
        'slug_field_description' => __('"Slug" er den SEO-vennlige versjonen av url-en. Eksempel /oslo', 'kursagenten'),
        'parent_field_description' => __('Velg en forelder for å lage et hierarki, og la dette bli en subkategori.', 'kursagenten'),
        'desc_field_description' => __('Kort beskrivelse brukes i oversikter og som innledende tekst på detaljside', 'kursagenten'),
    ),
    'public' => true,
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_menu' => false,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'rewrite' => array(
        'slug' => $kurssted,
    ),
));

// Registrering av taksonomien 'instruktorer'
register_taxonomy('ka_instructors', array('ka_course', 'ka_coursedate', 'instructor'), array(
    'labels' => array(
        'name' => __('Instruktører', 'kursagenten'),
        'singular_name' => __('Instruktør', 'kursagenten'),
        'menu_name' => __('Instruktører', 'kursagenten'),
        'all_items' => __('Alle instruktører', 'kursagenten'),
        'edit_item' => __('Rediger instruktør', 'kursagenten'),
        'view_item' => __('Vis instruktør', 'kursagenten'),
        'update_item' => __('Oppdater instruktør', 'kursagenten'),
        'add_new_item' => __('Legg til instruktør', 'kursagenten'),
        'new_item_name' => __('Navn på nytt instruktør', 'kursagenten'),
        'parent_item' => __('Overordnet instruktør', 'kursagenten'),
        'parent_item_colon' => __('Overordnet instruktør:', 'kursagenten'),
        'search_items' => __('Søk i instruktører', 'kursagenten'),
        'most_used' => __('Mest brukt', 'kursagenten'),
        'not_found' => __('Ingen instruktører funnet', 'kursagenten'),
        'no_terms' => __('Ingen instruktører', 'kursagenten'),
        'filter_by_item' => __('Filtrer på instruktør', 'kursagenten'),
        'items_list_navigation' => __('Instruktører listenavigasjon', 'kursagenten'),
        'items_list' => __('Instruktører liste', 'kursagenten'),
        'back_to_items' => __('← Tilbake til instruktører', 'kursagenten'),
        'item_link' => __('Instruktørlink', 'kursagenten'),
        'item_link_description' => __('Link til et instruktør', 'kursagenten'),
        'archives'  => __('Instruktører', 'kursagenten'),
        'name_field_description' => __('Navnet slik det som vises på siden. Bør kun endres på instruktørens brukerprofil på Kursagenten.', 'kursagenten'),
        'slug_field_description' => __('"Slug" er den SEO-vennlige versjonen av url-en. Eksempel /kari-norman', 'kursagenten'),
        'parent_field_description' => __('Velg en forelder for å lage et hierarki, og la dette bli en subkategori.', 'kursagenten'),
        'desc_field_description' => __('Kort beskrivelse brukes i oversikter og som innledende tekst på detaljside', 'kursagenten'),
    ),
    'public' => true,
    'hierarchical' => false,
    'show_ui' => true,
    'show_in_menu' => false,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'rewrite' => array(
        'slug' => $instruktor,
    ),
));

// Add taxonomies as submenus under Kursagenten main menu and reorganize menu order
add_action('admin_menu', function() {
    global $submenu;
    
    // Add taxonomy submenus under Kursagenten menu (parent 'kursagenten')
    add_submenu_page(
        'kursagenten',
        __('Kurskategorier', 'kursagenten'),
        __('Kurskategorier', 'kursagenten'),
        'manage_categories',                                // Capability
        'edit-tags.php?taxonomy=ka_coursecategory&post_type=ka_course', // Menu slug
        ''                                                  // Callback (empty for taxonomy links)
    );
    
    add_submenu_page(
        'kursagenten',
        __('Kurssteder', 'kursagenten'),
        __('Kurssteder', 'kursagenten'),
        'manage_categories',
        'edit-tags.php?taxonomy=ka_course_location&post_type=ka_course',
        ''
    );
    
    add_submenu_page(
        'kursagenten',
        __('Instruktører', 'kursagenten'),
        __('Instruktører', 'kursagenten'),
        'manage_categories',
        'edit-tags.php?taxonomy=ka_instructors&post_type=ka_course',
        ''
    );
}, 11); // Priority 11 to run after main menu (priority 9) but before reorganization

// Reorganize submenu order and add separators
add_action('admin_menu', function() {
    global $submenu;
    
    if (!isset($submenu['kursagenten'])) {
        return;
    }
    
    // Create a map of menu items by their slug for easy lookup
    $menu_items = [];
    foreach ($submenu['kursagenten'] as $item) {
        $menu_items[$item[2]] = $item;
    }
    
    // Define the desired order with separators
    $desired_order = [
        'kursagenten',                                                      // Oversikt
        'separator_1',                                                      // First separator
        'edit.php?post_type=ka_course',                                    // Alle kurs
        'edit-tags.php?taxonomy=ka_coursecategory&post_type=ka_course',   // Kurskategorier
        'edit-tags.php?taxonomy=ka_course_location&post_type=ka_course',  // Kurssteder
        'edit-tags.php?taxonomy=ka_instructors&post_type=ka_course',      // Instruktører
        'separator_2',                                                      // Second separator
        'design',                                                           // Kursdesign
        'kursinnstillinger',                                                // Synkronisering
        'bedriftsinformasjon',                                              // Bedriftsinformasjon
        'kursagenten-theme-customizations',                                 // Tematilpasninger
        'seo',                                                              // Endre url-er
        'avansert',                                                         // Avanserte innstillinger
        'ka_documentation',                                                 // Dokumentasjon
    ];
    
    // Build new submenu array in desired order
    $new_submenu = [];
    foreach ($desired_order as $slug) {
        if ($slug === 'separator_1' || $slug === 'separator_2') {
            // Add separator marker - we'll style the next item
            continue;
        }
        
        if (isset($menu_items[$slug])) {
            $item = $menu_items[$slug];
            
            // Add separator class to items after separators
            if ($slug === 'edit.php?post_type=ka_course') {
                $item[4] = 'kag-menu-separator-before';
            } elseif ($slug === 'design') {
                $item[4] = 'kag-menu-separator-before';
            }
            
            $new_submenu[] = $item;
        }
    }
    
    // Replace the submenu with our reorganized version
    $submenu['kursagenten'] = $new_submenu;
}, 999); // Very high priority to run after all other menu registrations

// Add link to Kursdatoer at the top of "Alle kurs" admin page
add_action('all_admin_notices', function() {
    $screen = get_current_screen();
    
    // Check if we're on the ka_course edit page
    if ($screen && $screen->post_type === 'ka_course' && $screen->base === 'edit') {
        $kursdatoer_url = admin_url('edit.php?post_type=ka_coursedate');
        ?>
        <div style="margin-top: 0px; padding: 12px 0; border-left-color: #2271b1;">
            <p style="margin: 0;">
                <strong><?php esc_html_e('Kursdatoer:', 'kursagenten'); ?></strong>
                <a href="<?php echo esc_url($kursdatoer_url); ?>"><?php esc_html_e('Se alle kursdatoer', 'kursagenten'); ?></a>
                <span style="color: #666; margin-left: 10px;"><?php esc_html_e('– Brukes for feilsøking og oversikt', 'kursagenten'); ?></span>
            </p>
        </div>
        <?php
    }
});

// Keep Kursagenten menu open when on taxonomy pages
add_filter('parent_file', function($parent_file) {
    global $current_screen;
    
    // Check if we're on one of our taxonomy edit pages
    if ($current_screen && in_array($current_screen->taxonomy, ['ka_coursecategory', 'ka_course_location', 'ka_instructors'])) {
        return 'kursagenten';
    }
    
    return $parent_file;
});

// Highlight the correct submenu item when on taxonomy pages
add_filter('submenu_file', function($submenu_file, $parent_file) {
    global $current_screen;
    
    // Only apply to our taxonomies under Kursagenten menu
    if ($parent_file === 'kursagenten' && $current_screen) {
        if ($current_screen->taxonomy === 'ka_coursecategory') {
            return 'edit-tags.php?taxonomy=ka_coursecategory&post_type=ka_course';
        } elseif ($current_screen->taxonomy === 'ka_course_location') {
            return 'edit-tags.php?taxonomy=ka_course_location&post_type=ka_course';
        } elseif ($current_screen->taxonomy === 'ka_instructors') {
            return 'edit-tags.php?taxonomy=ka_instructors&post_type=ka_course';
        }
    }
    
    return $submenu_file;
}, 10, 2);

// Make name field readonly for ka_course_location taxonomy and update description
add_action('ka_course_location_edit_form_fields', function($term) {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Make name field readonly
        $('#name').prop('readonly', true).css('background-color', '#f0f0f0');
        
        // Make slug field readonly
        $('#slug').prop('readonly', true).css('background-color', '#f0f0f0');
        
        // Update description text
        var $desc = $('#name-description');
        if ($desc.length) {
            $desc.html(<?php echo wp_json_encode(wp_kses_post(sprintf(
                __('Navnet slik det som vises på siden. Kan endres under <a href="%s">Synkronisering</a>.', 'kursagenten'),
                esc_url(admin_url('admin.php?page=kursinnstillinger#places'))
            ))); ?>);
        }
    });
    </script>
    <?php
}, 10, 1);

// Replace the add form with information message
add_action('ka_course_location_pre_add_form', function($taxonomy) {
    $sync_url = admin_url('admin.php?page=kursinnstillinger#places');
    $regions_url = admin_url('admin.php?page=kursinnstillinger#regions');
    $use_regions = get_option('kursagenten_use_regions', false);

    $location_info_title = __('Informasjon om kurssteder', 'kursagenten');
    $location_info_paragraphs = array(
        wp_kses_post(__('<strong>Kurssteder opprettes automatisk</strong> når du synkroniserer kurs fra Kursagenten. Du kan ikke legge til kurssteder manuelt her.', 'kursagenten')),
        wp_kses_post(sprintf(
            __('<strong>Navnendring på kurssteder:</strong><br>Du kan endre navn på kurssteder under <a href="%s">Synkronisering → Navnendring på kurssteder</a>. Når du endrer navn på et sted, blir også slugs (nettadressen) på kursene som har dette stedet oppdatert.<br> Det gamle stedet blir ikke slettet, men blir ikke lenger synlig på nettsiden.', 'kursagenten'),
            esc_url($sync_url)
        )),
    );
    if ($use_regions) {
        $location_info_paragraphs[] = wp_kses_post(sprintf(
            __('<strong>Regioner:</strong><br>Regioner er aktivert. Du kan administrere regioninndelingen under <a href="%s">Synkronisering → Regioner</a>. Tilhørighet til en region kan endres under hvert kurssted.', 'kursagenten'),
            esc_url($regions_url)
        ));
    } else {
        $location_info_paragraphs[] = wp_kses_post(sprintf(
            __('<strong>Regioner:</strong><br>Du kan aktivere og administrere regioner under <a href="%s">Synkronisering → Regioner</a>.', 'kursagenten'),
            esc_url($regions_url)
        ));
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var locationInfoTitle = <?php echo wp_json_encode($location_info_title); ?>;
        var locationInfoParagraphs = <?php echo wp_json_encode($location_info_paragraphs); ?>;

        setTimeout(function() {
            var $formWrap = $('#col-left .form-wrap');
            if (!$formWrap.length) {
                return;
            }

            $formWrap.find('form#addtag').hide();
            $formWrap.find('h2').text(locationInfoTitle);

            var noticeHtml = '<div class="notice notice-info" style="padding: 15px; margin: 15px 0;">';
            locationInfoParagraphs.forEach(function(paragraph, index) {
                var marginBottom = index === locationInfoParagraphs.length - 1 ? '0' : '10px';
                noticeHtml += '<p style="margin-bottom: ' + marginBottom + ';">' + paragraph + '</p>';
            });
            noticeHtml += '</div>';

            $formWrap.find('h2').after(noticeHtml);
        }, 100);
    });
    </script>
    <?php
});

// Store original term name and slug before update to prevent changes
add_action('load-edit-tags.php', function() {
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'ka_course_location' && isset($_GET['tag_ID'])) {
        $term_id = (int) $_GET['tag_ID'];
        $term = get_term($term_id, 'ka_course_location');
        if ($term && !is_wp_error($term)) {
            // Store original name and slug in transient
            set_transient('ka_location_original_name_' . $term_id, $term->name, 300);
            set_transient('ka_location_original_slug_' . $term_id, $term->slug, 300);
        }
    }
});

// Prevent name and slug changes via form submission
add_action('edit_term', function($term_id, $tt_id, $taxonomy) {
    static $preventing_loop = false;
    
    // Only apply to ka_course_location taxonomy
    if ($taxonomy !== 'ka_course_location' || $preventing_loop) {
        return;
    }
    
    // Get original name and slug from transient
    $original_name = get_transient('ka_location_original_name_' . $term_id);
    $original_slug = get_transient('ka_location_original_slug_' . $term_id);
    
    $needs_revert = false;
    $update_data = array();
    
    // Check if name was changed
    if ($original_name && isset($_POST['name']) && $_POST['name'] !== $original_name) {
        $update_data['name'] = $original_name;
        $needs_revert = true;
    }
    
    // Check if slug was changed
    if ($original_slug && isset($_POST['slug']) && $_POST['slug'] !== $original_slug) {
        $update_data['slug'] = $original_slug;
        $needs_revert = true;
    }
    
    if ($needs_revert) {
        // Revert the changes
        $preventing_loop = true;
        wp_update_term($term_id, $taxonomy, $update_data);
        $preventing_loop = false;
        
        // Delete transients
        delete_transient('ka_location_original_name_' . $term_id);
        delete_transient('ka_location_original_slug_' . $term_id);
        
        // Show admin notice
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php echo wp_kses_post(sprintf(
                    __('<strong>Advarsel:</strong> Navn og slug på kurssteder kan ikke endres her. Du kan endre navnet under <a href="%s">Synkronisering</a>.', 'kursagenten'),
                    esc_url(admin_url('admin.php?page=kursinnstillinger#places'))
                )); ?></p>
            </div>
            <?php
        });
    } else {
        // Clean up transients
        delete_transient('ka_location_original_name_' . $term_id);
        delete_transient('ka_location_original_slug_' . $term_id);
    }
}, 5, 3); // Priority 5 to run early
