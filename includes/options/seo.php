<?php
class SEO {
    private $kag_seo_options;


    public function __construct() {
        add_action('admin_menu', array($this, 'kag_seo_add_plugin_page'));
        add_action('admin_init', array($this, 'kag_seo_page_init'));
        add_action('update_option_kag_seo_option_name', array($this, 'flush_rewrite_rules_on_update'), 10, 2);
        //add_action('update_option_course_slug', 'flush_rewrite_rules');
    }

    public function kag_seo_add_plugin_page() {
        add_submenu_page(
            'kursagenten',         // Parent slug
            'Url-er', // page_title
            'URL-er og SEO', // menu_title
            'manage_options',      // capability
            'seo', // menu_slug
            array($this, 'kag_seo_create_admin_page')
            //, // function
            //'dashicons-store',     // icon_url
            //2                      // position
        );
    }

    public function kag_seo_create_admin_page() {
        $this->kag_seo_options = get_option('kag_seo_option_name'); 
        $hidden_url_conflicts = $this->get_hidden_url_conflicts();
        
        ?>
        <div class="wrap options-form ka-wrap" id="toppen">
        <form method="post" action="options.php">
            <?php kursagenten_sticky_admin_menu(); ?>
            <h1>SEO innstillinger</h1><br><br>

                <?php
                settings_fields('kag_seo_option_group');
                do_settings_sections('seo-admin');
                ?>

                <!-- Fyll ut feltene under -->
                <div class="options-card">
                <h3 id="url">Endre url prefix</h3>
                <p><strong>Viktig info om url-er</strong><br>Her kan du endre url for kurs, instruktør, kurskategori og kurssted. <span style="color:#b74444;font-weight:bold;">OBS! Ikke rør med mindre du vet hva du gjør.</span> Det kan ødelegge nettstedet, og gjøre disse sidene utilgjengelige. Husk å lagre <a href="/wp-admin/options-permalink.php" target="_blank">permalenkeinnstillingene</a> etter du har gjort en endring.</p>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Kurs</th>
                        <td>
                            <input class="regular-text" type="text" name="kag_seo_option_name[ka_url_rewrite_kurs]" value="<?php echo isset($this->kag_seo_options['ka_url_rewrite_kurs']) ? esc_attr($this->kag_seo_options['ka_url_rewrite_kurs']) : ''; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Instruktør</th>
                        <td>
                            <input class="regular-text" type="text" name="kag_seo_option_name[ka_url_rewrite_instruktor]" value="<?php echo isset($this->kag_seo_options['ka_url_rewrite_instruktor']) ? esc_attr($this->kag_seo_options['ka_url_rewrite_instruktor']) : ''; ?>">
                            <label style="margin-left: 12px;">
                                <input type="checkbox" name="kag_seo_option_name[ka_url_hide_instruktor]" value="1" <?php checked(isset($this->kag_seo_options['ka_url_hide_instruktor']) && $this->kag_seo_options['ka_url_hide_instruktor']); ?>>
                                Skjul i url-er <span class="ka-tooltip" data-title="Skjuler du prefix i url-er, kan det oppstå konflikt med vanlige sider, innlegg eller andre taksonomier med samme slug. Se informasjon om fallback-mekanismen nedenfor." style="display:inline-flex;vertical-align:middle;cursor:help;"><i class="ka-icon icon-notice" aria-hidden="true" style="margin-left:6px;"></i></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Kurskategori</th>
                        <td>
                            <input class="regular-text" type="text" name="kag_seo_option_name[ka_url_rewrite_kurskategori]" value="<?php echo isset($this->kag_seo_options['ka_url_rewrite_kurskategori']) ? esc_attr($this->kag_seo_options['ka_url_rewrite_kurskategori']) : ''; ?>">
                            <label style="margin-left: 12px;">
                                <input type="checkbox" name="kag_seo_option_name[ka_url_hide_kurskategori]" value="1" <?php checked(isset($this->kag_seo_options['ka_url_hide_kurskategori']) && $this->kag_seo_options['ka_url_hide_kurskategori']); ?>>
                                Skjul i url-er <span class="ka-tooltip" data-title="Skjuler du prefix i url-er, kan det oppstå konflikt med vanlige sider, innlegg eller andre taksonomier med samme slug. Se informasjon om fallback-mekanismen nedenfor." style="display:inline-flex;vertical-align:middle;cursor:help;"><i class="ka-icon icon-notice" aria-hidden="true" style="margin-left:6px;"></i></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Kurssted</th>
                        <td>
                            <input class="regular-text" type="text" name="kag_seo_option_name[ka_url_rewrite_kurssted]" value="<?php echo isset($this->kag_seo_options['ka_url_rewrite_kurssted']) ? esc_attr($this->kag_seo_options['ka_url_rewrite_kurssted']) : ''; ?>">
                            <label style="margin-left: 12px;">
                                <input type="checkbox" name="kag_seo_option_name[ka_url_hide_kurssted]" value="1" <?php checked(isset($this->kag_seo_options['ka_url_hide_kurssted']) && $this->kag_seo_options['ka_url_hide_kurssted']); ?>>
                                Skjul i url-er <span class="ka-tooltip" data-title="Skjuler du prefix i url-er, kan det oppstå konflikt med vanlige sider, innlegg eller andre taksonomier med samme slug. Se informasjon om fallback-mekanismen nedenfor." style="display:inline-flex;vertical-align:middle;cursor:help;"><i class="ka-icon icon-notice" aria-hidden="true" style="margin-left:6px;"></i></span>
                            </label>
                        </td>
                    </tr>

                </table>

                <p class="description" style="max-width:900px;margin-top:10px;">
                    <strong>Hvordan fallback fungerer:</strong> Når "Skjul i url-er" er aktivert, forsøker vi først kort URL uten prefix.
                    Hvis slugen er i konflikt med en side/innlegg eller en annen skjult taksonomi, brukes automatisk prefiks-URL
                    (f.eks. <code>/kurskategori/slug/</code> eller din tilpassede verdi som <code>/kat/slug/</code>).
                    Vanlige WordPress-sider og innlegg får alltid prioritet ved konflikt.
                </p>

                <?php if (!empty($hidden_url_conflicts)) : ?>
                    <div class="notice notice-warning inline" style="margin-top:14px;">
                        <p><strong>Konflikter funnet for skjulte URL-er:</strong> Disse slugene bruker fallback med prefix.</p>
                        <ul style="margin: 0 0 8px 18px; list-style: disc;">
                            <?php foreach ($hidden_url_conflicts as $conflict) : ?>
                                <li>
                                    <code><?php echo esc_html($conflict['slug']); ?></code>
                                    – <?php echo esc_html($conflict['context']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                </div>

                <!-- SEO på/av og dokumentasjon -->
                <div class="options-card">
                <h3 id="seo">SEO på kurs og taksonomisider</h3>
                <p>Kursagenten legger til meta-tagger, Open Graph, Twitter Cards og Course-schema på kurs- og taksonomisider. Når du har en SEO-utvidelse installert, tilpasser vi oss automatisk for å unngå duplikater. <br>Du kan også skru av vår SEO helt hvis du bruker andre løsninger.</p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Skru av SEO</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kag_seo_option_name[ka_seo_disable]" value="1" <?php checked(isset($this->kag_seo_options['ka_seo_disable']) && $this->kag_seo_options['ka_seo_disable']); ?>>
                                Skru av SEO på kurs og taksonomisider
                            </label>
                            <p class="description">Aktiver dette hvis du bruker andre SEO-utvidelser som ikke er listet under, eller ønsker å håndtere SEO helt selv.</p>
                        </td>
                    </tr>
                </table>

                <h4 style="margin-top: 1.5em;">Støttede SEO-utvidelser</h4>
                <p class="description">Når disse er aktive, slår vi av våre meta-tagger og overlater til utvidelsen. Course-schema leveres av oss for de som ikke har det innebygd.</p>
                <table class="widefat striped" style="max-width: 900px; margin-top: 0.5em;">
                    <thead>
                        <tr>
                            <th>Utvidelse</th>
                            <th>Vår SEO av (meta-tagger)</th>
                            <th>Vår Course-schema</th>
                            <th>Våre tilpasninger (tittel/beskrivelse)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Yoast SEO</td><td>✓</td><td>✓</td><td>Instruktør-tittel</td></tr>
                        <tr><td>Rank Math</td><td>✓</td><td>Av (har egen)</td><td>Instruktør-tittel</td></tr>
                        <tr><td>All in One SEO</td><td>✓</td><td>✓</td><td>–</td></tr>
                        <tr><td>Slim SEO</td><td>✓</td><td>✓</td><td>Instruktør-tittel, kurs tittel/beskrivelse</td></tr>
                        <tr><td>SEOPress</td><td>✓</td><td>✓</td><td>Instruktør-tittel, kurs tittel/beskrivelse</td></tr>
                        <tr><td>The SEO Framework</td><td>✓</td><td>✓</td><td>Instruktør-tittel, kurs tittel/beskrivelse</td></tr>
                    </tbody>
                </table>

                <h4 style="margin-top: 1.5em;">Hva Kursagenten legger til (når ingen SEO-utvidelse er aktiv)</h4>
                <table class="widefat striped" style="max-width: 900px; margin-top: 0.5em;">
                    <thead>
                        <tr>
                            <th>Element</th>
                            <th>Kurs</th>
                            <th>Taksonomier (kategori, sted, instruktør)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Canonical URL</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Meta description</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Open Graph (Facebook, LinkedIn)</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Twitter Cards</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Course-schema (JSON-LD)</td><td>✓</td><td>–</td></tr>
                    </tbody>
                </table>
                </div>

                <?php submit_button(); ?>

    <?php
    kursagenten_admin_footer();
    }

    public function kag_seo_page_init() {
        register_setting(
            'kag_seo_option_group', // option_group
            'kag_seo_option_name',  // option_name
            array($this, 'kag_seo_sanitize') // sanitize_callback
        );

        // Sections have been removed since fields are directly integrated in the form HTML
    }

    public function kag_seo_sanitize($input) {
        $sanitary_values = array();
        // Defensiv sjekk for å unngå fatale feil ved uventede typer
        if (!is_array($input)) {
            error_log('Kursagenten: kag_seo_sanitize expected array, got ' . gettype($input));
            $existing = get_option('kag_seo_option_name', array());
            return is_array($existing) ? $existing : array();
        }

        try {
            // Checkbox: when unchecked it's not in POST, so default to 0
            $sanitary_values['ka_seo_disable'] = isset($input['ka_seo_disable']) && $input['ka_seo_disable'] ? '1' : '0';
            $sanitary_values['ka_url_hide_instruktor'] = isset($input['ka_url_hide_instruktor']) && $input['ka_url_hide_instruktor'] ? '1' : '0';
            $sanitary_values['ka_url_hide_kurskategori'] = isset($input['ka_url_hide_kurskategori']) && $input['ka_url_hide_kurskategori'] ? '1' : '0';
            $sanitary_values['ka_url_hide_kurssted'] = isset($input['ka_url_hide_kurssted']) && $input['ka_url_hide_kurssted'] ? '1' : '0';

            foreach ($input as $key => $value) {
                if (in_array($key, array('ka_seo_disable', 'ka_url_hide_instruktor', 'ka_url_hide_kurskategori', 'ka_url_hide_kurssted'), true)) {
                    continue; // Already handled
                }
                $sanitary_values[$key] = sanitize_text_field($value);
            }
        } catch (\Throwable $e) {
            error_log('Kursagenten: kag_seo_sanitize error: ' . $e->getMessage());
            $existing = get_option('kag_seo_option_name', array());
            return is_array($existing) ? $existing : array();
        }

        return $sanitary_values;
    }
    
    public function flush_rewrite_rules_on_update($old_value, $new_value) {
        // Sjekk om noen av URL-innstillingene har endret seg
        $url_fields = array(
            'ka_url_rewrite_kurs',
            'ka_url_rewrite_instruktor',
            'ka_url_rewrite_kurskategori',
            'ka_url_rewrite_kurssted',
            'ka_url_hide_instruktor',
            'ka_url_hide_kurskategori',
            'ka_url_hide_kurssted'
        );
        $has_changes = false;
        
        foreach ($url_fields as $field) {
            $old_field_value = isset($old_value[$field]) ? (string) $old_value[$field] : '';
            $new_field_value = isset($new_value[$field]) ? (string) $new_value[$field] : '';
            if ($old_field_value !== $new_field_value) {
                $has_changes = true;
                break;
            }
        }
        
        if ($has_changes) {
            flush_rewrite_rules();
            // Clear menu cache so automeny URLs use the new slugs
            if (function_exists('kursagenten_clear_all_menu_caches')) {
                kursagenten_clear_all_menu_caches();
            }
        }
    }

    /**
     * Build config for taxonomies that can hide URL prefix.
     *
     * @return array<string, array<string, string>>
     */
    private function get_hide_url_taxonomy_config() {
        return array(
            'ka_coursecategory' => array(
                'label' => 'Kurskategori',
                'option' => 'ka_url_hide_kurskategori',
            ),
            'ka_course_location' => array(
                'label' => 'Kurssted',
                'option' => 'ka_url_hide_kurssted',
            ),
            'ka_instructors' => array(
                'label' => 'Instruktør',
                'option' => 'ka_url_hide_instruktor',
            ),
        );
    }

    /**
     * Resolve the slug used in hidden URL mode for a term.
     *
     * @param string  $taxonomy Taxonomy name.
     * @param WP_Term $term     Taxonomy term.
     * @return string
     */
    private function get_hidden_url_term_slug($taxonomy, $term) {
        if ($taxonomy !== 'ka_instructors' || !($term instanceof WP_Term)) {
            return $term instanceof WP_Term ? (string) $term->slug : '';
        }

        $name_display = get_option('kursagenten_taxonomy_ka_instructors_name_display', '');
        if ($name_display === '' || $name_display === false) {
            $name_display = get_option('kursagenten_taxonomy_instructors_name_display', '');
        }

        if ($name_display === 'firstname' || $name_display === 'lastname') {
            $meta_key = $name_display === 'firstname' ? 'instructor_firstname' : 'instructor_lastname';
            $display_name = get_term_meta($term->term_id, $meta_key, true);
            if (!empty($display_name)) {
                return sanitize_title((string) $display_name);
            }
        }

        return (string) $term->slug;
    }

    /**
     * Get all detected hidden URL conflicts for enabled taxonomies.
     *
     * @return array<int, array<string, string>>
     */
    private function get_hidden_url_conflicts() {
        $options = is_array($this->kag_seo_options) ? $this->kag_seo_options : array();
        $taxonomy_config = $this->get_hide_url_taxonomy_config();
        $slug_map = array();
        $conflicts = array();

        foreach ($taxonomy_config as $taxonomy => $config) {
            $is_hidden = isset($options[$config['option']]) && (string) $options[$config['option']] === '1';
            if (!$is_hidden) {
                continue;
            }

            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $slug = $this->get_hidden_url_term_slug($taxonomy, $term);
                if ($slug === '') {
                    continue;
                }

                if (!isset($slug_map[$slug])) {
                    $slug_map[$slug] = array();
                }

                $slug_map[$slug][] = $config['label'] . ': ' . $term->name;
            }
        }

        // Conflict with pages/posts
        foreach ($slug_map as $slug => $entries) {
            if (function_exists('kursagenten_get_public_post_by_slug')) {
                $matched_post = kursagenten_get_public_post_by_slug($slug);
            } else {
                $matched_post = null;
            }

            if ($matched_post instanceof WP_Post) {
                $post_type_object = get_post_type_object($matched_post->post_type);
                $post_type_label = $post_type_object ? $post_type_object->labels->singular_name : $matched_post->post_type;

                $conflicts[] = array(
                    'slug' => $slug,
                    'context' => sprintf('%s har samme slug som %s "%s"', implode(', ', $entries), $post_type_label, get_the_title($matched_post)),
                );
            }
        }

        // Conflict across hidden taxonomies/terms
        foreach ($slug_map as $slug => $entries) {
            if (count($entries) > 1) {
                $conflicts[] = array(
                    'slug' => $slug,
                    'context' => 'Samme slug brukes flere steder: ' . implode(', ', $entries),
                );
            }
        }

        // Ensure stable order and no duplicates.
        usort($conflicts, static function ($a, $b) {
            return strcmp($a['slug'], $b['slug']);
        });

        $unique = array();
        $seen = array();
        foreach ($conflicts as $conflict) {
            $key = $conflict['slug'] . '|' . $conflict['context'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $conflict;
        }

        return $unique;
    }
}

?>