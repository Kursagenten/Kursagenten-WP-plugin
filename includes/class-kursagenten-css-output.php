<?php
/**
 * Class for handling CSS output based on theme settings
 */
class Kursagenten_CSS_Output {
    public function __construct() {
        add_action('wp_head', array($this, 'output_custom_css'), 999);
    }

    public function output_custom_css() {
        $css = ':root {';
        
        // Maksbredde
        $max_width = get_option('kursagenten_max_width', '1300px');
        $css .= '--ka-max-width: ' . esc_attr($max_width) . ';';
        
        // Hovedfarge og avledede farger
        $main_color = get_option('kursagenten_main_color', 'hsl(32, 96%, 49%)');
        
        // Konverter til HSL hvis det er hex
        if (strpos($main_color, '#') === 0) {
            list($h, $s, $l) = $this->hex_to_hsl($main_color);
        } else {
            $hsl = str_replace(['hsl(', ')', '%'], '', $main_color);
            $values = explode(',', $hsl);
            $h = trim($values[0]);
            $s = trim($values[1]);
            $l = trim($values[2]);
        }

        $css .= '--ka-color: ' . esc_attr($main_color) . ';';
        
        // Juster metningsgrad og lyshet basert på utgangspunktet
        $base_l = floatval($l);
        
        // For mørkere farger (under 50% lyshet), reduser metningen for lysere varianter
        if ($base_l < 50) {
            $light_s = max(floatval($s) * 0.7, 30); // Reduser metning med 30%, men ikke under 30%
            $css .= '--ka-color-darker: ' . "hsl($h, {$s}%, " . max(0, $base_l - 10) . "%);";
            $css .= '--ka-color-lighter: ' . "hsl($h, {$light_s}%, " . min(100, $base_l + 11) . "%);";
            $css .= '--ka-color-light: ' . "hsl($h, {$light_s}%, " . min(100, $base_l + 30) . "%);";
            $css .= '--ka-color-lightest: ' . "hsl($h, {$light_s}%, " . min(100, $base_l + 47) . "%);";
            $css .= '--ka-color-light-hover: ' . "hsl($h, {$light_s}%, " . min(100, $base_l + 35) . "%);";
            $css .= '--ka-color-light-active: ' . "hsl($h, {$light_s}%, " . min(100, $base_l + 40) . "%);";
        } 
        // For lysere farger, behold mer av metningen
        else {
            $css .= '--ka-color-darker: ' . "hsl($h, {$s}%, " . max(0, $base_l - 10) . "%);";
            $css .= '--ka-color-lighter: ' . "hsl($h, {$s}%, " . min(100, $base_l + 11) . "%);";
            $css .= '--ka-color-light: ' . "hsl($h, {$s}%, " . min(100, $base_l + 30) . "%);";
            $css .= '--ka-color-lightest: ' . "hsl($h, {$s}%, " . min(100, $base_l + 47) . "%);";
            $css .= '--ka-color-light-hover: ' . "hsl($h, {$s}%, " . min(100, $base_l + 35) . "%);";
            $css .= '--ka-color-light-active: ' . "hsl($h, {$s}%, " . min(100, $base_l + 40) . "%);";
        }
        
        // Aksentfarge og avledede farger (samme logikk som over)
        $accent_color = get_option('kursagenten_accent_color', 'hsl(310, 45%, 52%)');
        $css .= '--ka-color-accent: ' . esc_attr($accent_color) . ';';
        
        // Konverter aksentfarge til HSL hvis det er hex
        if (strpos($accent_color, '#') === 0) {
            list($accent_h, $accent_s, $accent_l) = $this->hex_to_hsl($accent_color);
        } else {
            $accent_hsl = str_replace(['hsl(', ')', '%'], '', $accent_color);
            $accent_values = explode(',', $accent_hsl);
            $accent_h = trim($accent_values[0]);
            $accent_s = trim($accent_values[1]);
            $accent_l = trim($accent_values[2]);
        }
        
        $base_accent_l = floatval($accent_l);
        
        // Juster aksentfarger basert på utgangspunktet
        if ($base_accent_l < 50) {
            $light_accent_s = max(floatval($accent_s) * 0.8, 30);
            $css .= '--ka-color-accent-hover: ' . "hsl($accent_h, {$light_accent_s}%, " . min(100, $base_accent_l + 10) . "%);";
            $css .= '--ka-color-accent-active: ' . "hsl($accent_h, {$light_accent_s}%, " . min(100, $base_accent_l + 15) . "%);";
            $css .= '--ka-color-accent-disabled: ' . "hsl($accent_h, {$light_accent_s}%, " . min(100, $base_accent_l + 25) . "%);";
            $css .= '--ka-color-accent-light: ' . "hsl($accent_h, {$light_accent_s}%, " . min(100, $base_accent_l + 35) . "%);";
            $css .= '--ka-color-accent-dark: ' . "hsl($accent_h, {$accent_s}%, " . max(0, $base_accent_l - 15) . "%);";
        } else {
            $css .= '--ka-color-accent-hover: ' . "hsl($accent_h, {$accent_s}%, " . min(100, $base_accent_l + 6) . "%);";
            $css .= '--ka-color-accent-active: ' . "hsl($accent_h, {$accent_s}%, " . min(100, $base_accent_l + 12) . "%);";
            $css .= '--ka-color-accent-disabled: ' . "hsl($accent_h, {$accent_s}%, " . min(100, $base_accent_l + 18) . "%);";
            $css .= '--ka-color-accent-light: ' . "hsl($accent_h, {$accent_s}%, " . min(100, $base_accent_l + 25) . "%);";
            $css .= '--ka-color-accent-dark: ' . "hsl($accent_h, {$accent_s}%, " . max(0, $base_accent_l - 8) . "%);";
        }
        

        
        // Base skriftstørrelse
        $base_font = get_option('kursagenten_base_font', '16px');
        $css .= '--ka-base-font: ' . esc_attr($base_font) . ';';
        
        // Font size levels (calculated based on base font)
        $css .= '--ka-font-xxs: calc(var(--ka-base-font) * 0.68);';
        $css .= '--ka-font-xs: calc(var(--ka-base-font) * 0.75);';
        $css .= '--ka-font-s: calc(var(--ka-base-font) * 0.875);';
        $css .= '--ka-font-s-plus: calc(var(--ka-base-font) * 0.9375);';
        $css .= '--ka-font-base: var(--ka-base-font);';
        $css .= '--ka-font-md: calc(var(--ka-base-font) * 1.125);';
        $css .= '--ka-font-lg: calc(var(--ka-base-font) * 1.375);';
        $css .= '--ka-font-xl: calc(var(--ka-base-font) * 1.625);';
        $css .= '--ka-font-xxl: calc(var(--ka-base-font) * 2);';
        
        // Line heights
        $css .= '--ka-line-height-tight: 1.2;';
        $css .= '--ka-line-height-normal: 1.5;';
        $css .= '--ka-line-height-relaxed: 1.75;';
        
        // Additional CSS variables
        $css .= '--ka-alt-background: rgba(0, 0, 0, 0.02);';
        $css .= '--ka-box-background: rgba(0, 0, 0, 0.02);';
        $css .= '--ka-color-filter: #494949;';
        $css .= '--ka-filter-font-size: 14px;';
        $css .= '--ka-font-size-small: 0.775rem;';
        $css .= '--ka-font-size-medium: 1rem;';
        
        // Hovedoverskrift font
        $heading_font = get_option('kursagenten_heading_font', 'inherit');
        if ($heading_font !== 'inherit') {
            $css .= '--ka-font-family-main-headings: ' . esc_attr($heading_font) . ';';
        }
        
        // Hovedfont
        $main_font = get_option('kursagenten_main_font', 'inherit');
        if ($main_font !== 'inherit') {
            $css .= '--ka-font-family: ' . esc_attr($main_font) . ';';
        }
        
        // Sjekk om avanserte farger er aktivert
        $advanced_colors = get_option('kursagenten_advanced_colors', 0);
        
        if ($advanced_colors) {
            // Knappefarger
            $button_background = get_option('kursagenten_button_background', '');
            $button_color = get_option('kursagenten_button_color', '');
            if ($button_background) {
                $css .= '--ka-button-background: ' . esc_attr($button_background) . ';';
                $css .= '--ka-button-background-lighter: ' . $this->adjust_lightness($button_background, 10) . ';';
                $css .= '--ka-button-background-darker: ' . $this->adjust_lightness($button_background, -10) . ';';
                
                // Endre farger på knapper
                $css .= '#ka .pagination .current {border-color: var(--ka-button-background); background: var(--ka-button-background); color: var(--ka-button-color);}';
                $css .= '#ka .pagination a:hover, #ka .pagination a:focus, #ka .pagination a:active { border-color: var(--ka-button-background);}';
                $css .= '#ka button:not(.compact-more-dates-link) { background: var(--ka-button-background); color: var(--ka-button-color); border: none; }';
                $css .= '#ka .courselist-button { background: var(--ka-button-background); color: var(--ka-button-color); border: none; }';
                $css .= '#ka .ka-button { background: var(--ka-button-background); color: var(--ka-button-color); border: none; }';
                $css .= '#ka .pamelding:not(.signup-link) { background: var(--ka-button-background); color: var(--ka-button-color); border: none; }';
                $css .= '#ka .button { background: var(--ka-button-background); color: var(--ka-button-color); border: none; }';
                
                // Hover-effekter for knapper
                $css .= '#ka button:not(.compact-more-dates-link, .chip):hover, #ka .courselist-button:hover, #ka .ka-button:hover, #ka .pamelding:not(.signup-link):hover, #ka .button:hover { background: var(--ka-button-background-darker); }';
                $css .= '#ka button:not(.compact-more-dates-link, .chip):focus, #ka .courselist-button:focus, #ka .ka-button:focus, #ka .pamelding:not(.signup-link):focus, #ka .button:focus { background: var(--ka-button-background-darker); }';
                $css .= '#ka button:not(.compact-more-dates-link, .chip):active, #ka .courselist-button:active, #ka .ka-button:active, #ka .pamelding:not(.signup-link):active, #ka .button:active { background: var(--ka-button-background-darker); }';
            }
            if ($button_color) {
                $css .= '--ka-button-color: ' . esc_attr($button_color) . ';';
            }
            
            // Hvis kun bakgrunnsfarge er satt, bruk standard tekstfarge
            if ($button_background && !$button_color) {
                $css .= '#ka button:not(.compact-more-dates-link), #ka .courselist-button, #ka .ka-button, #ka .pamelding:not(.signup-link), #ka .button { color: #ffffff; }';
            }
            
            // Hvis kun tekstfarge er satt, bruk standard bakgrunnsfarge
            if ($button_color && !$button_background) {
                $css .= '#ka button:not(.compact-more-dates-link), #ka .courselist-button, #ka .ka-button, #ka .pamelding:not(.signup-link), #ka .button { background: var(--ka-color); }';
            }

            // Linker
            $link_color = get_option('kursagenten_link_color', '');
            if ($link_color) {
                $css .= '--ka-link-color: ' . esc_attr($link_color) . ';';
                $css .= '--ka-link-color-lighter: ' . $this->adjust_lightness($link_color, 10) . ';';
                $css .= '--ka-link-color-darker: ' . $this->adjust_lightness($link_color, -10) . ';';
                
                // Endre farger på linker
                $css .= '#ka a:not(.courselist-button):not(.course-title a):not(.course-linkk):not(.ka-button):not(.button):not(.header-links a):not(.button-filter) { color: var(--ka-link-color); }';
                $css .= '#ka a:not(.courselist-button):not(.course-title a):not(.course-linkk):not(.ka-button):not(.button):not(.header-links a):not(.button-filter):hover { color: var(--ka-link-color-darker); }';
                $css .= '#ka a:not(.courselist-button):not(.course-title a):not(.course-linkk):not(.ka-button):not(.button):not(.header-links a):not(.button-filter):focus { color: var(--ka-link-color-darker); }';
                $css .= '#ka a:not(.courselist-button):not(.course-title a):not(.course-linkk):not(.ka-button):not(.button):not(.header-links a):not(.button-filter):active { color: var(--ka-link-color-darker); }';
            }
            
            // Hvis kun linkfarge er satt, bruk standard hover-farge
            if ($link_color) {
                $css .= '#ka a:not(.courselist-button):not(.course-linkk):not(.ka-button):not(.button):not(.header-links a):not(.button-filter):hover { color: var(--ka-link-color-darker); }';
            }

            // Ikoner
            $icon_color = get_option('kursagenten_icon_color', '');
            if ($icon_color) {
                $css .= '--ka-icon-color: ' . esc_attr($icon_color) . ';';
                $css .= '--ka-icon-color-lighter: ' . $this->adjust_lightness($icon_color, 10) . ';';
                $css .= '--ka-icon-color-darker: ' . $this->adjust_lightness($icon_color, -10) . ';';
                
                // Endre farger på ikoner
                $css .= '#ka .ka-icon { background-color: var(--ka-icon-color); }';
                $css .= '#ka .ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .iconlist i { background-color: var(--ka-icon-color); }';
                $css .= '#ka .iconlist i:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .iconlist .ka-icon { background-color: var(--ka-icon-color); }';
                $css .= '#ka .iconlist .ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .header-links .ka-icon { background-color: var(--ka-icon-color); }';
                $css .= '#ka .header-links .ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .maps-link .ka-icon { background-color: var(--ka-icon-color); }';
                $css .= '#ka .maps-link .ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .taxonomy-list .ka-icon { background-color: var(--ka-icon-color); }';
                $css .= '#ka .taxonomy-list .ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .course-container .header-content i.ka-icon { background-color: var(--ka-icon-color-lighter); }';
                $css .= '#ka .course-container .header-content i.ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
                $css .= '#ka .ka-container .courselist-main .meta-area .accordion-icon { color: var(--ka-icon-color); }';
                
                // Generell regel for alle ikoner som ikke er knapper eller linker
                $css .= '#ka i.ka-icon:not(.courselist-button i):not(.course-link i):not(.ka-button i):not(.button i):not(.course-container .header-content i) { background-color: var(--ka-icon-color); }';
                $css .= '#ka i.ka-icon:not(.courselist-button i):not(.course-link i):not(.ka-button i):not(.button i):not(.course-container .header-content i):hover { background-color: var(--ka-icon-color-darker); }';
            }
            
            // Hvis kun ikonfarge er satt, bruk standard hover-farge
            if ($icon_color) {
                $css .= '#ka .ka-icon:hover, #ka .iconlist i:hover, #ka .iconlist .ka-icon:hover, #ka .header-links .ka-icon:hover, #ka .maps-link .ka-icon:hover, #ka .taxonomy-list .ka-icon:hover, #ka .course-container .header-content i.ka-icon:hover { background-color: var(--ka-icon-color-darker); }';
            }

            // Sidebakgrunn
            $background_color = get_option('kursagenten_background_color', '');
            if ($background_color) {
                $css .= '--ka-background-color: ' . esc_attr($background_color) . ';';
                $css .= '--ka-background-color-lighter: ' . $this->adjust_lightness($background_color, 10) . ';';
                $css .= '--ka-background-color-darker: ' . $this->adjust_lightness($background_color, -10) . ';';
                
                // Endre sidebakgrunn
                $css .= '.ka-default-width, .kursagenten-full-width, #ka { background-color: var(--ka-background-color); }';
                //$css .= '#ka .ka-section { background-color: var(--ka-background-color); }';
            }
            
            // Hvis kun bakgrunnsfarge er satt, bruk standard tekstfarge
            if ($background_color) {
                $css .= '#ka { color: inherit; }';
            }

            // Bakgrunn fremhevede områder og Alternativ farge for bokser
            // If only highlight is set: both --ka-highlight-background and --ka-box-background get the same color
            // If only box is set: only --ka-box-background gets the color
            $highlight_background = get_option('kursagenten_highlight_background', '');
            $box_background = get_option('kursagenten_box_background', '');

            if ($highlight_background) {
                $css .= '--ka-highlight-background: ' . esc_attr($highlight_background) . ';';
                $css .= '--ka-highlight-background-lighter: ' . $this->adjust_lightness($highlight_background, 5) . ';';
                $css .= '--ka-highlight-background-darker: ' . $this->adjust_lightness($highlight_background, -10) . ';';
                if (!$box_background) {
                    $css .= '--ka-box-background: ' . esc_attr($highlight_background) . ';';
                    $css .= '--ka-box-background-lighter: ' . $this->adjust_lightness($highlight_background, 5) . ';';
                    $css .= '--ka-box-background-darker: ' . $this->adjust_lightness($highlight_background, -10) . ';';
                }
            }
            if ($box_background) {
                $css .= '--ka-box-background: ' . esc_attr($box_background) . ';';
                $css .= '--ka-box-background-lighter: ' . $this->adjust_lightness($box_background, 5) . ';';
                $css .= '--ka-box-background-darker: ' . $this->adjust_lightness($box_background, -10) . ';';
            }

            // If either background is set, ensure text inherits properly
            if ($highlight_background || $box_background) {
                $css .= '#ka .ka-section, #ka .options-card, #ka .courselist-item { color: inherit; }';
            }
        }
        
        $css .= '}';

        // Active single-course design. Hero overrides apply to designs that use the
        // hero header (default, sidebar-image), but NOT to compact which has its own
        // header/infostripe color settings and would otherwise be hit by .ka-header rules.
        $single_design = get_option('kursagenten_single_design', 'default');

        // Hero header overrides – Enkeltkurs (single default / sidebar-image)
        $single_hero_overlay = get_option('kursagenten_single_hero_header_overlay', 'dark');
        $single_hero_font_color = get_option('kursagenten_single_hero_header_font_color', '');
        $single_hero_bg_color = get_option('kursagenten_single_hero_header_bg_color', '');

        if (!in_array($single_hero_overlay, ['light', 'dark'], true)) {
            $single_hero_overlay = 'dark';
        }
        if ($single_design !== 'compact') :
        if ($single_hero_overlay === 'light') {
            $css .= '#ka .course-container .ka-header.hero-overlay-light:not(.no-hero-image) .overlay { ';
            $css .= 'background: linear-gradient(to bottom, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.9)) !important; } ';
            $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.hero-overlay-light:not(.no-hero-image)') . ' { color: #222 !important; } ';
        } else {
            $css .= '#ka .course-container .ka-header.hero-overlay-dark:not(.no-hero-image) .overlay { ';
            $css .= 'background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.9)) !important; } ';
            $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.hero-overlay-dark:not(.no-hero-image)') . ' { color: #fff !important; } ';
        }
        if (!empty($single_hero_font_color)) {
            $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.hero-overlay-light') . ', ';
            $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header') . ' { color: ' . esc_attr($single_hero_font_color) . ' !important; } ';
        }
        if (!empty($single_hero_bg_color)) {
            $css .= '#ka .course-container .ka-header.hero-bgcolor-only .background-blur, ';
            $css .= '#ka .course-container .ka-header.hero-bgcolor-only .ka-content-container, ';
            $css .= '#ka .course-container .ka-header.no-hero-image .background-blur, ';
            $css .= '#ka .course-container .ka-header.no-hero-image .ka-content-container { ';
            $css .= 'background-color: ' . esc_attr($single_hero_bg_color) . ' !important; background-image: none !important; } ';
            $css .= '#ka .course-container .ka-header.no-hero-image .overlay { display: none !important; } ';
        }

        $single_hero_bg_mode = get_option('kursagenten_single_hero_header_bg_mode', 'image_placeholder');
        if (empty($single_hero_font_color) && $single_hero_bg_mode === 'image_bgcolor') {
            if ($single_hero_overlay === 'light') {
                $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.no-hero-image.hero-overlay-light') . ' { color: #222 !important; } ';
            } else {
                $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.no-hero-image.hero-overlay-dark') . ' { color: #fff !important; } ';
            }
        } elseif (empty($single_hero_font_color) && $single_hero_bg_mode === 'bgcolor_only') {
            $css .= $this->hero_header_content_children_selector('#ka .course-container .ka-header.no-hero-image') . ' { color: #222 !important; } ';
        }
        endif; // $single_design !== 'compact'

        // Compact design overrides – header + infostripe colors
        if ($single_design === 'compact') {
            $compact_header_bg_color       = get_option('kursagenten_compact_header_bg_color', '');
            $compact_header_font_color     = get_option('kursagenten_compact_header_font_color', '');
            $compact_header_link_color     = get_option('kursagenten_compact_header_link_color', '');
            $compact_infostripe_bg_color   = get_option('kursagenten_compact_infostripe_bg_color', '');
            $compact_infostripe_font_color = get_option('kursagenten_compact_infostripe_font_color', '');
            $compact_infostripe_link_color = get_option('kursagenten_compact_infostripe_link_color', '');

            // Header background (+ panel uses the same tone, lightened with white)
            if (!empty($compact_header_bg_color)) {
                $css .= '#ka .course-container-compact { --ka-compact-panel-bg: color-mix(in srgb, ' . esc_attr($compact_header_bg_color) . ' 92%, #fff 30%); } ';
                $css .= '#ka .course-container-compact .ka-compact-header { background-color: ' . esc_attr($compact_header_bg_color) . ' !important; background-image: none !important; } ';
            }
            // Header text (title, subtitle, nav text – excludes links and the signup button).
            // Explicit font color wins; otherwise auto-pick readable text from the bg color.
            $compact_header_text_color = '';
            if (!empty($compact_header_font_color)) {
                $compact_header_text_color = $compact_header_font_color;
            } elseif (!empty($compact_header_bg_color)) {
                $compact_header_text_color = $this->auto_contrast_text_color($compact_header_bg_color);
            }
            if (!empty($compact_header_text_color)) {
                $css .= '#ka .course-container-compact .ka-compact-header .header-content > h1, ';
                $css .= '#ka .course-container-compact .ka-compact-header .compact-header-subtitle, ';
                $css .= '#ka .course-container-compact .ka-compact-header .compact-header-subtitle .compact-header-date, ';
                $css .= '#ka .course-container-compact .ka-compact-header .header-links, ';
                $css .= '#ka .course-container-compact .ka-compact-header .header-links.iconlist a, ';
                $css .= '#ka .course-container-compact .ka-compact-header .header-links .taxonomy-list, ';
                $css .= '#ka .course-container-compact .ka-compact-header .header-links .separator { color: ' . esc_attr($compact_header_text_color) . ' !important; } ';
            }
            // Header links (location link + nav links, not the signup button).
            // Explicit link color wins; otherwise derive a readable accent from the bg.
            $compact_header_link = '';
            if (!empty($compact_header_link_color)) {
                $compact_header_link = $compact_header_link_color;
            } elseif (!empty($compact_header_bg_color)) {
                $compact_header_link = $this->auto_link_color($compact_header_bg_color);
            }
            if (!empty($compact_header_link)) {
                $css .= '#ka .course-container-compact .ka-compact-header .compact-header-location, ';
                $css .= '#ka .course-container-compact .ka-compact-header .header-links:not(.iconlist) a { color: ' . esc_attr($compact_header_link) . ' !important; } ';
                // Icons are mask-based, so they are colored via background-color.
                $css .= '#ka .course-container-compact .ka-compact-header .header-links i.ka-icon { background-color: ' . esc_attr($compact_header_link) . ' !important; } ';
            }
            // Infostripe background (panel keeps header-based --ka-compact-panel-bg)
            if (!empty($compact_infostripe_bg_color)) {
                $css .= '#ka .course-container-compact .ka-infostripe { background-color: ' . esc_attr($compact_infostripe_bg_color) . ' !important; background-image: none !important; } ';
            }
            // Infostripe text (headings + body text, excludes links).
            // Explicit font color wins; otherwise auto-pick readable text from the bg color.
            $compact_infostripe_text_color = '';
            if (!empty($compact_infostripe_font_color)) {
                $compact_infostripe_text_color = $compact_infostripe_font_color;
            } elseif (!empty($compact_infostripe_bg_color)) {
                $compact_infostripe_text_color = $this->auto_contrast_text_color($compact_infostripe_bg_color);
            }
            if (!empty($compact_infostripe_text_color)) {
                $css .= '#ka .course-container-compact .ka-infostripe, ';
                $css .= '#ka .course-container-compact .ka-infostripe h2, ';
                $css .= '#ka .course-container-compact .ka-infostripe span, ';
                $css .= '#ka .course-container-compact .ka-infostripe .iconlist { color: ' . esc_attr($compact_infostripe_text_color) . ' !important; } ';
            }
            // Infostripe links + icons. Explicit link color wins; otherwise derive a
            // readable accent from the bg. Icons are mask-based (background-color).
            $compact_infostripe_link = '';
            if (!empty($compact_infostripe_link_color)) {
                $compact_infostripe_link = $compact_infostripe_link_color;
            } elseif (!empty($compact_infostripe_bg_color)) {
                $compact_infostripe_link = $this->auto_link_color($compact_infostripe_bg_color);
            }
            if (!empty($compact_infostripe_link)) {
                $css .= '#ka .course-container-compact .ka-infostripe a, ';
                $css .= '#ka .course-container-compact .compact-infostripe-panel a:not(.courselist-button.pameldingskjema) { color: ' . esc_attr($compact_infostripe_link) . ' !important; } ';
                $css .= '#ka .course-container-compact .ka-infostripe i.ka-icon, ';
                $css .= '#ka .course-container-compact .compact-infostripe-panel i.ka-icon { background-color: ' . esc_attr($compact_infostripe_link) . ' !important; } ';
                $css .= '#ka .course-container-compact .compact-more-dates-link { color: ' . esc_attr($compact_infostripe_link) . ' !important; } ';
                $css .= '#ka .course-container-compact .compact-more-dates-link:hover, ';
                $css .= '#ka .course-container-compact .compact-more-dates-link:focus-visible { color: color-mix(in srgb, ' . esc_attr($compact_infostripe_link) . ' 82%, #000 18%) !important; } ';
            }
        }

        // Hero header overrides – Taksonomisider
        $taxonomy_hero_overlay = get_option('kursagenten_taxonomy_hero_header_overlay', 'dark');
        if (!in_array($taxonomy_hero_overlay, ['light', 'dark'], true)) {
            $taxonomy_hero_overlay = 'dark';
        }
        $taxonomy_hero_font_color = get_option('kursagenten_taxonomy_hero_header_font_color', '');
        $taxonomy_hero_bg_color = get_option('kursagenten_taxonomy_hero_header_bg_color', '');

        if ($taxonomy_hero_overlay === 'light') {
            $css .= '#ka .taxonomy-hero-header.hero-overlay-light:not(.no-hero-image) .overlay { ';
            $css .= 'background: linear-gradient(to bottom, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.9)) !important; } ';
            $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.hero-overlay-light:not(.no-hero-image)') . ', ';
            $css .= '#ka .taxonomy-hero-header.hero-overlay-light:not(.no-hero-image) .taxonomy-description, ';
            $css .= '#ka .taxonomy-hero-header.hero-overlay-light:not(.no-hero-image) .taxonomy-header-content h1 { color: #222 !important; } ';
        } else {
            $css .= '#ka .taxonomy-hero-header.hero-overlay-dark:not(.no-hero-image) .overlay { ';
            $css .= 'background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.9)) !important; } ';
            $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.hero-overlay-dark:not(.no-hero-image)') . ', ';
            $css .= '#ka .taxonomy-hero-header.hero-overlay-dark:not(.no-hero-image) .taxonomy-description, ';
            $css .= '#ka .taxonomy-hero-header.hero-overlay-dark:not(.no-hero-image) .taxonomy-header-content h1, ';
            $css .= '#ka .taxonomy-hero-header.hero-overlay-dark:not(.no-hero-image) .taxonomy-read-more-link { color: #fff !important; } ';
        }
        if (!empty($taxonomy_hero_font_color)) {
            $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header') . ', ';
            $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.hero-overlay-light') . ', ';
            $css .= '#ka .taxonomy-hero-header .taxonomy-description { color: ' . esc_attr($taxonomy_hero_font_color) . ' !important; } ';
        }
        if (!empty($taxonomy_hero_bg_color)) {
            $css .= '#ka .taxonomy-hero-header.no-hero-image .background-blur, ';
            $css .= '#ka .taxonomy-hero-header.no-hero-image .ka-content-container { ';
            $css .= 'background-color: ' . esc_attr($taxonomy_hero_bg_color) . ' !important; background-image: none !important; } ';
            $css .= '#ka  .taxonomy-hero-header.no-hero-image .overlay { display: none !important; } ';
        }

        // No taxonomy image: use overlay preset or bgcolor-only defaults unless font color is set
        $taxonomy_hero_bg_mode = get_option('kursagenten_taxonomy_hero_header_bg_mode', 'image_placeholder');
        if (empty($taxonomy_hero_font_color) && $taxonomy_hero_bg_mode === 'image_bgcolor') {
            if ($taxonomy_hero_overlay === 'light') {
                $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.no-hero-image.hero-overlay-light') . ', ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-light .taxonomy-description, ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-light .taxonomy-header-content h1, ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-light .taxonomy-read-more-link { color: #222 !important; } ';
            } else {
                $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.no-hero-image.hero-overlay-dark') . ', ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-dark .taxonomy-description, ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-dark .taxonomy-header-content h1, ';
                $css .= '#ka .taxonomy-hero-header.no-hero-image.hero-overlay-dark .taxonomy-read-more-link { color: #fff !important; } ';
            }
        } elseif (empty($taxonomy_hero_font_color) && $taxonomy_hero_bg_mode === 'bgcolor_only') {
            $css .= $this->hero_header_content_children_selector('#ka .taxonomy-hero-header.no-hero-image') . ', ';
            $css .= '#ka .taxonomy-hero-header.no-hero-image .taxonomy-description, ';
            $css .= '#ka .taxonomy-hero-header.no-hero-image .taxonomy-header-content h1 { color: #222 !important; } ';
        }

        // Output CSS
        echo '<style type="text/css" id="kursagenten-custom-css">' . $css . '</style>';
    }

    /**
     * Specific selectors for hero header text color (excludes buttons and accent links).
     *
     * The $prefix is applied to every selector in the list so none leak globally.
     *
     * @param string $prefix Parent scope prepended to each selector.
     * @return string CSS selector fragment.
     */
    private function hero_header_content_children_selector($prefix = '') {
        if (function_exists('kursagenten_get_hero_header_text_selectors')) {
            return kursagenten_get_hero_header_text_selectors($prefix);
        }

        $targets = ['.header-content > h1', '.header-content .header-links', '.header-content .header-links a'];
        $prefix = trim((string) $prefix);
        if ($prefix !== '') {
            $targets = array_map(static function ($selector) use ($prefix) {
                return $prefix . ' ' . $selector;
            }, $targets);
        }

        return implode(', ', $targets);
    }

    /**
     * Picks a readable text color (#fff or #222) for a given background color.
     *
     * @param string $bg_color Background color (hex, rgb(a) or hsl(a)).
     * @return string '#fff' for dark backgrounds, otherwise '#222'.
     */
    private function auto_contrast_text_color($bg_color) {
        return $this->is_dark_background($bg_color) ? '#fff' : '#222';
    }

    /**
     * Picks a readable link/icon color for a given background color.
     *
     * Uses a very light tint of the main color on dark backgrounds and a darker
     * variant on light backgrounds, so links keep the brand accent while staying
     * legible. These CSS variables are emitted in :root.
     *
     * @param string $bg_color Background color (hex, rgb(a) or hsl(a)).
     * @return string CSS color value (a var() referencing the main color).
     */
    private function auto_link_color($bg_color) {
        return $this->is_dark_background($bg_color)
            ? 'var(--ka-color-lightest, #eef3f3)'
            : 'var(--ka-color-darker, var(--ka-color))';
    }

    /**
     * Determines whether a color is perceptually dark.
     *
     * Supports hex (#rgb / #rrggbb), rgb()/rgba() and hsl()/hsla(). Unknown
     * formats fall back to "not dark" so the default dark text is used.
     *
     * @param string $color Color value.
     * @return bool True when the color is dark enough to need light text.
     */
    private function is_dark_background($color) {
        $color = trim((string) $color);
        if ($color === '') {
            return false;
        }

        if ($color[0] === '#') {
            $hex = ltrim($color, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            if (strlen($hex) < 6 || !ctype_xdigit(substr($hex, 0, 6))) {
                return false;
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } elseif (preg_match('/rgba?\(([^)]+)\)/i', $color, $m)) {
            $parts = array_map('trim', explode(',', $m[1]));
            $r = isset($parts[0]) ? (float) $parts[0] : 0;
            $g = isset($parts[1]) ? (float) $parts[1] : 0;
            $b = isset($parts[2]) ? (float) $parts[2] : 0;
        } elseif (preg_match('/hsla?\(([^)]+)\)/i', $color, $m)) {
            $parts = array_map('trim', explode(',', $m[1]));
            $lightness = isset($parts[2]) ? (float) str_replace('%', '', $parts[2]) : 50.0;
            return $lightness < 55.0;
        } else {
            return false;
        }

        // Perceived luminance (0-255). Below ~140 reads as dark.
        $luminance = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
        return $luminance < 140.0;
    }

    /**
     * Konverterer hex-farge til HSL-verdier
     * 
     * @param string $hex Hex fargekode (f.eks. '#8e0063')
     * @return array Array med [hue, saturation, lightness]
     */
    private function hex_to_hsl($hex) {
        // Fjern # hvis den finnes
        $hex = ltrim($hex, '#');
        
        // Konverter til RGB
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        
        // Beregn luminance først
        $l = ($max + $min) / 2;
        
        // Hvis max og min er like, er det en gråtone
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            
            // Beregn saturation
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            
            // Beregn hue
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            
            $h = $h / 6;
        }
        
        // Konverter til HSL-verdier
        $h = round($h * 360);
        $s = round($s * 100);
        $l = round($l * 100);
        
        return [$h, $s, $l];
    }

    /**
     * Justerer lysheten på en farge
     * 
     * @param string $color Farge i hex eller hsl format
     * @param int $amount Mengde å justere lysheten med (-100 til 100)
     * @return string Justert farge i samme format som input
     */
    private function adjust_lightness($color, $amount) {
        if (strpos($color, '#') === 0) {
            list($h, $s, $l) = $this->hex_to_hsl($color);
            $l = max(0, min(100, $l + $amount));
            return "hsl($h, {$s}%, {$l}%)";
        } else {
            $hsl = str_replace(['hsl(', ')', '%'], '', $color);
            list($h, $s, $l) = explode(',', $hsl);
            $l = max(0, min(100, floatval($l) + $amount));
            return "hsl($h, {$s}%, {$l}%)";
        }
    }
} 