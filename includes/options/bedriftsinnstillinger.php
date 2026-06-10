<?php
class Bedriftsinformasjon {
    private $bedriftsinfo_options;


    public function __construct() {
        add_action('admin_menu', array($this, 'bedriftsinfo_add_plugin_page'));
        add_action('admin_init', array($this, 'bedriftsinfo_page_init'));
    }

    public function bedriftsinfo_add_plugin_page() {
        add_submenu_page(
            'kursagenten',
            __('Bedriftsinformasjon', 'kursagenten'),
            __('Bedriftsinformasjon', 'kursagenten'),
            'manage_options',
            'bedriftsinformasjon',
            array($this, 'bedriftsinfo_create_admin_page')
        );
    }

    public function bedriftsinfo_create_admin_page() {
        $this->bedriftsinfo_options = get_option('kag_bedriftsinfo_option_name'); 

        ?>
        <div class="wrap options-form ka-wrap" id="toppen">
        <form method="post" action="options.php">
        <?php kursagenten_sticky_admin_menu(); ?>
        <h1><?php esc_html_e('Bedriftsinformasjon', 'kursagenten'); ?></h1>
        <p><?php echo wp_kses_post(__('Her kan du skrive inn informasjon som vil bli brukt ulike steder på nettsiden. Dette inkluderer navn på hovedkontakt (personvernerklæring), samt firmanavn og adresse (kontaktside og bunnfelt).<br>Du kan også legge inn informasjon om bedriften som kan vises på med kortkode. Se alle kortkoder <a href="#kortkoder">her</a>.', 'kursagenten')); ?></p>

        <?php
        settings_fields('bedriftsinfo_option_group');
        do_settings_sections('bedriftsinfo-admin');
        ?>

        <div class="options-card">
        <h3><?php esc_html_e('Firmainformasjon', 'kursagenten'); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Firmanavn', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_firmanavn]" value="<?php echo isset($this->bedriftsinfo_options['ka_firmanavn']) ? esc_attr($this->bedriftsinfo_options['ka_firmanavn']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Adresse', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_adresse]" value="<?php echo isset($this->bedriftsinfo_options['ka_adresse']) ? esc_attr($this->bedriftsinfo_options['ka_adresse']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Postnr/sted', 'kursagenten'); ?></th>
                <td>
                    <input style="width:10em;float:left;" class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_postnummer]" value="<?php echo isset($this->bedriftsinfo_options['ka_postnummer']) ? esc_attr($this->bedriftsinfo_options['ka_postnummer']) : ''; ?>">
                    <input style="width:15em;float:left;" class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_sted]" value="<?php echo isset($this->bedriftsinfo_options['ka_sted']) ? esc_attr($this->bedriftsinfo_options['ka_sted']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Hovedkontakt', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_hovedkontakt_navn]" value="<?php echo isset($this->bedriftsinfo_options['ka_hovedkontakt_navn']) ? esc_attr($this->bedriftsinfo_options['ka_hovedkontakt_navn']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Epost', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_epost]" value="<?php echo isset($this->bedriftsinfo_options['ka_epost']) ? esc_attr($this->bedriftsinfo_options['ka_epost']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Telefon', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_tlf]" value="<?php echo isset($this->bedriftsinfo_options['ka_tlf']) ? esc_attr($this->bedriftsinfo_options['ka_tlf']) : ''; ?>">
                </td>
            </tr>
        </table>
        </div>
        <div class="options-card">
        <h3><?php esc_html_e('Om bedriften', 'kursagenten'); ?></h3>
        <p><?php esc_html_e('Her kan du skrive inn kort informasjon om bedriften. Denne teksten kan vises på med kortkode.', 'kursagenten'); ?></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Om firmaet', 'kursagenten'); ?></th>
                <td>
                    <textarea class="large-text" rows="4" name="kag_bedriftsinfo_option_name[ka_infotekst]"><?php echo isset($this->bedriftsinfo_options['ka_infotekst']) ? esc_textarea($this->bedriftsinfo_options['ka_infotekst']) : ''; ?></textarea>
                </td>
            </tr>
        </table>
        </div>
        <div class="options-card">
        <h3><?php esc_html_e('Sosiale profiler', 'kursagenten'); ?></h3>
        <p><?php esc_html_e('Her kan du skrive inn URL til sosiale profiler. Disse kan bli brukt rundt på nettsiden.', 'kursagenten'); ?></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Facebook', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_facebook]" value="<?php echo isset($this->bedriftsinfo_options['ka_facebook']) ? esc_attr($this->bedriftsinfo_options['ka_facebook']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Instagram', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_instagram]" value="<?php echo isset($this->bedriftsinfo_options['ka_instagram']) ? esc_attr($this->bedriftsinfo_options['ka_instagram']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('LinkedIn', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_linkedin]" value="<?php echo isset($this->bedriftsinfo_options['ka_linkedin']) ? esc_attr($this->bedriftsinfo_options['ka_linkedin']) : ''; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('YouTube', 'kursagenten'); ?></th>
                <td>
                    <input class="regular-text" type="text" name="kag_bedriftsinfo_option_name[ka_youtube]" value="<?php echo isset($this->bedriftsinfo_options['ka_youtube']) ? esc_attr($this->bedriftsinfo_options['ka_youtube']) : ''; ?>">
                </td>
            </tr>
        </table>
        </div>

    <?php
    kursagenten_admin_footer();
    }

    public function bedriftsinfo_page_init() {
        register_setting(
            'bedriftsinfo_option_group',
            'kag_bedriftsinfo_option_name',
            array($this, 'bedriftsinfo_sanitize')
        );
    }

    public function bedriftsinfo_sanitize($input) {
        $sanitary_values = array();
        if (!is_array($input)) {
            error_log('Kursagenten: bedriftsinfo_sanitize expected array, got ' . gettype($input));
            $existing = get_option('kag_bedriftsinfo_option_name', array());
            return is_array($existing) ? $existing : array();
        }

        try {
            foreach ($input as $key => $value) {
                $sanitary_values[$key] = sanitize_text_field($value);
            }
        } catch (\Throwable $e) {
            error_log('Kursagenten: bedriftsinfo_sanitize error: ' . $e->getMessage());
            $existing = get_option('kag_bedriftsinfo_option_name', array());
            return is_array($existing) ? $existing : array();
        }

        return $sanitary_values;
    }
}

?>
