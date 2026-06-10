<?php
// Documentation admin page for Kursagenten
// Comments in English; UI text in Norwegian

class KA_Documentation_Page {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    public function add_plugin_page() {
        add_submenu_page(
            'kursagenten',
            __('Dokumentasjon', 'kursagenten'),
            __('Dokumentasjon', 'kursagenten'),
            'manage_options',
            'ka_documentation',
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap options-form ka-wrap" id="toppen">
            <form method="post" action="options.php">
                <?php kursagenten_sticky_admin_menu(false); ?>
                <h1><?php esc_html_e('Dokumentasjon', 'kursagenten'); ?></h1>

                <div class="options-card">
                    <h3><?php esc_html_e('Kom i gang – A–Å', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Denne siden hjelper deg raskt i gang med å sette opp Kursagenten-pluginen fra A til Å. Følg stegene i rekkefølge, og bruk venstremenyen for å hoppe mellom seksjoner.', 'kursagenten'); ?></p>
                    <ol>
                        <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=bedriftsinformasjon">Bedriftsinformasjon</a> og fyll ut grunnleggende firmaopplysninger.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Åpne <a href="admin.php?page=kursinnstillinger">Synkronisering</a> og koble mot Kursagenten-kontoen din. Legg inn <a href="https://kursadmin.kursagenten.no/IntegrationSettings" target="_blank">Webhooks</a> i Kursagenten, så blir kurset overført automatisk når det blir lagret/opprettet. <br>Første gang bør du klikke på "Hent alle kurs fra Kursagenten". Da henter du alle kursene samtidig.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<a href="admin.php?page=design">Kursdesign</a> – Opprett ønskede sider for kurs, kategorier, steder og instruktører via "Wordpress sider". Kortkoder legges inn automatisk.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Velg filtre på kursliste i <a href="admin.php?page=design">Kursdesign</a>, og også andre designvalg (liste/grid, detaljer, og malvalg).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Sjekk <a href="admin.php?page=seo">Endre url-er</a> om du må tilpasse url-strukturer. Her kan du endre fra feks. "/kurs/ditt-kurs" til "/undervisning/ditt-kurs".', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Du kan legge inn menypunkter som automatisk generer kurskategorier, kurssteder og instruktører. Mer informasjon i seksjonen <a href="#menyer">Menyer</a>.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Kurskategorier, kurssteder og instruktører kan berikes med tekst og bilder om ønsket. Mer informasjon i seksjonen <a href="#berike-taksonomisider">Berike taksonomisider</a>.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Test frontend: kursliste, enkeltkurs, kategorier, kurssteder og instruktører, og juster filtrene/design ved behov.', 'kursagenten'); ?></li>
                    </ol>
                </div>

                <h2><?php esc_html_e('Kursdata fra Kursagenten', 'kursagenten'); ?></h2>

                <div id="synkronisering" class="options-card">
                    <h3><?php esc_html_e('Synkronisering av kurs', 'kursagenten'); ?></h3>
                    
                    <h4><?php esc_html_e('Henting av kurs fra Kursagenten', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Kursene dine hentes fra Kursagenten på to måter:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Manuell synkronisering:</strong> Gå til <a href="admin.php?page=kursinnstillinger">Synkronisering</a> og klikk på "Hent alle kurs fra Kursagenten". Dette bør gjøres første gang du setter opp pluginen, og ved behov for å oppdatere alle kursene samtidig.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Automatisk synkronisering via webhooks:</strong> Når webhooks er konfigurert, oppdateres kursene automatisk når de endres i Kursagenten. Dette er den anbefalte metoden for løpende oppdateringer.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Opprydding av kurs', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Opprydding fjerner kurs og kursdatoer som ikke lenger finnes i Kursagenten, samt utløpte kursdatoer. Dette holder nettsiden ryddig og oppdatert.', 'kursagenten'); ?></p>
                    <p><?php esc_html_e('Det finnes to måter å rydde opp på:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Avkrysning før synkronisering:</strong> Når du klikker på "Hent alle kurs fra Kursagenten", kan du kryss av for "Rydd opp i kurs etter synkronisering". Dette kjører opprydding automatisk etter at synkroniseringen er fullført. <strong>NB:</strong> Opprydding tar 3-5 minutter ekstra.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Egen opprydding:</strong> Du kan også kjøre opprydding separat ved å klikke på knappen "Rydd opp i kurs" på <a href="admin.php?page=kursinnstillinger">Synkronisering</a>-siden. Dette er nyttig hvis du bare ønsker å rydde opp uten å synkronisere alle kursene på nytt.', 'kursagenten')); ?></li>
                    </ul>
                    <p><strong><?php esc_html_e('Hva ryddes opp:', 'kursagenten'); ?></strong></p>
                    <ul>
                        <li><?php esc_html_e('Kurs som er slettet i Kursagenten (finnes ikke lenger i API-et)', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Kursdatoer som er slettet eller utløpt', 'kursagenten'); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('<strong>Automatisk opprydding:</strong> Det kjøres også en automatisk nattlig opprydding klokken 03:00 hver natt, så det er ikke alltid nødvendig å kjøre opprydding manuelt. Bruk manuell opprydding hvis du har mange utløpte kursdatoer som vises på nettsiden og ønsker å rydde opp umiddelbart.', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Webhooks', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Webhooks sikrer at kursene dine alltid er oppdatert uten manuell innsats. For å aktivere webhooks:', 'kursagenten'); ?></p>
                    <ol>
                        <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=kursinnstillinger">Synkronisering</a> i WordPress-administrasjonen.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Kopier webhook-URL-en som vises på siden:', 'kursagenten'); ?> <code class="copytext"><?php echo esc_url(site_url('/wp-json/kursagenten-api/v1/process-webhook')); ?></code></li>
                        <li><?php echo wp_kses_post(__('Logg inn på <a href="https://kursadmin.kursagenten.no/IntegrationSettings" target="_blank">Kursagenten</a> og gå til <strong>Integrasjonsinnstillinger → Webhooks</strong>.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Lim inn webhook-URL-en i feltene <strong>CourseCreated</strong> og <strong>CourseUpdated</strong>.', 'kursagenten')); ?></li>
                    </ol>
                    <p><?php esc_html_e('Når webhooks er aktivert, vil kursene automatisk oppdateres i WordPress når de endres eller opprettes i Kursagenten. Dette gjelder både kursdata, datoer og lokasjoner.', 'kursagenten'); ?></p>
                </div>

                <div id="stedsnavn-og-regioner" class="options-card">
                    <h3><?php esc_html_e('Stedsnavn og regioner', 'kursagenten'); ?></h3>
                    <h4><?php esc_html_e('Endring av stedsnavn', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du kan endre navn på kurssteder som kommer fra Kursagenten. Dette er nyttig hvis du ønsker å bruke kortere eller mer beskrivende navn på nettsiden.', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=kursinnstillinger#location-name-mapping">Synkronisering → Navnendring på kurssteder</a>.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Klikk på "Endre navn på nytt sted" for å legge til en ny navnendring.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Velg stedet fra listen eller skriv inn navnet manuelt, og angi det nye navnet.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Klikk "Lagre" for å lagre navnendringen.', 'kursagenten'); ?></li>
                    </ul>
                    <p><strong><?php esc_html_e('Viktig:', 'kursagenten'); ?></strong> <?php esc_html_e('Når du endrer navn på et sted:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Det gamle stedet blir ikke slettet, men blir ikke lenger synlig på nettsiden.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Slugs (nettadresser) på kursene som har dette stedet oppdateres ved neste synkronisering.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Du må kjøre en full synkronisering ("Hent alle kurs fra Kursagenten") for å ta i bruk navnendringene.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Merk også "Rydd opp i kurs" før du kjører synken for å sikre at gamle kursdatoer fjernes.', 'kursagenten'); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Regioner', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Regioner lar deg organisere kurssteder i geografiske områder (Sørlandet, Østlandet, Vestlandet, Midt-Norge, Nord-Norge). Dette er nyttig for filtrering og organisering av kurssteder.', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=kursinnstillinger#regions">Synkronisering → Regioner</a>.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Kryss av for "Aktiver regioninndeling" for å aktivere funksjonen.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Dra fylker mellom regionene for å organisere dem etter dine behov.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Endringene lagres automatisk når du flytter fylker.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Bruk "Resett til standard" for å tilbakestille regioninndelingen til standardverdiene.', 'kursagenten'); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('<strong>Bruk i kortkoder:</strong> Du kan filtrere kurssteder basert på region i kortkoden <code class="copytext">[kurssteder]</code>:', 'kursagenten')); ?></p>
                    <ul>
                        <li><code class="copytext">[kurssteder region="østlandet"]</code> – <?php esc_html_e('viser kun kurssteder i Østlandet', 'kursagenten'); ?></li>
                        <li><code class="copytext">[kurssteder region="østlandet" vis="bergen"]</code> – <?php esc_html_e('viser alle steder i Østlandet eller Bergen (OR-logikk)', 'kursagenten'); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('Gyldige regioner: <code>sørlandet</code>, <code>østlandet</code>, <code>vestlandet</code>, <code>midt-norge</code>, <code>nord-norge</code>', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Merk:</strong> Regioner må være aktivert for at filtreringen skal fungere. Du kan også endre region for individuelle kurssteder ved å redigere kursstedet i <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Kurssteder</a>-oversikten.', 'kursagenten')); ?></p>
                </div>

                <h2><?php esc_html_e('Sider og systemsider', 'kursagenten'); ?></h2>

                <div id="anbefalte-sider" class="options-card">
                    <h3><?php esc_html_e('Sider som opprettes automatisk', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Pluginen oppretter automatisk systemsider for enkeltsider som håndteres av pluginen. Disse sidene genereres dynamisk og trenger ikke å opprettes manuelt:', 'kursagenten'); ?></p>
                    <h4><?php esc_html_e('Enkeltkurs', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Hvert kurs har sin egen side som genereres automatisk basert på valgt designmal. Innholdet hentes fra Kursagenten, inkludert bildet. Du kan legge til eget innhold mellom introtekst og hovedinnhold ved å redigere kurset. Du ser en markering for dette området når du besøker siden som administrator, samt en link for å gå til redigering. Det er også snarvei for å redigere kurset i Kursagenten.  Se etter et blyant-ikon du kan klikke på. Dette åpner kurset i Kursagenten, i en ny fane. Gjør endringene du ønsker der, lagre, og så ser du umiddelbart endringene på nettsiden.', 'kursagenten'); ?></p>                    <ul>
                    
                    <h4><?php esc_html_e('Taksonomisider', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Hver kategori, hvert sted og hver instruktør får automatisk sin egen side. Disse genereres fra maler som velges i <a href="admin.php?page=design">Kursdesign</a>. Du kan berike disse sidene med ekstra innhold, bilder og tekst ved å redigere taksonomiene.', 'kursagenten')); ?></p>
                    
                    <p><?php echo wp_kses_post(__('Alle systemsider følger WordPress sin standard template-hierarki, så hvis temaet ditt har egne templates (f.eks. <code>single-ka_course.php</code> eller <code>taxonomy-ka_coursecategory.php</code>), vil disse brukes i stedet for pluginen sin standardmal.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Page builder (Kadence Elements, Elementor m.fl.):</strong> Hvis du vil styre single/archive/taksonomi med page builder-betingelser, gå til <a href="admin.php?page=design#section-enkeltkurs">Kursdesign → Enkeltkurs</a> og aktiver <em>Bruk WordPress sitt standard malhierarki</em>. Da lar pluginen tema/page builder velge mal uten å tvinge pluginens egne layout-filer.', 'kursagenten')); ?></p>
                </div>

                <div id="sider-som-ma-opprettes" class="options-card">
                    <h3><?php esc_html_e('Sider som må opprettes', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('For å vise oversikter og lister med kurs, kategorier, steder og instruktører, må du opprette vanlige WordPress-sider med kortkoder. Disse sidene bør opprettes fra <a href="admin.php?page=design#section-systemsider">Kursdesign → Wordpress sider</a>. Har du allerede sider du ønsker å bruke, velger du dem fra dropdown-menyen.', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Oversiktssider med kortkoder', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Under <a href="admin.php?page=design#section-systemsider">Kursdesign → Wordpress sider</a> kan du opprette følgende sider:', 'kursagenten')); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Kurs</strong> – Inneholder <code class="copytext">[kursliste]</code> for å vise alle kurs med filtre.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kurskategorier</strong> – Inneholder <code class="copytext">[kurskategorier]</code> for å vise alle kategorier.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kurssteder</strong> – Inneholder <code class="copytext">[kurssteder]</code> for å vise alle kurssteder.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Instruktører</strong> – Inneholder <code class="copytext">[instruktorer]</code> for å vise alle instruktører.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Betalingsside', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Det opprettes også automatisk en side for betaling når pluginen aktiveres. Denne siden inneholder kode som genererer betalingsboksen og er nødvendig for at betalingsfunksjonaliteten skal fungere. Betalingssiden publiseres automatisk, mens de andre oversiktssidene opprettes som kladd (draft) slik at du kan redigere dem før publisering.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Hvordan opprette sidene', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du har flere alternativer:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Automatisk opprettelse:</strong> Gå til <a href="admin.php?page=design#section-systemsider">Kursdesign → Wordpress sider</a> og klikk på "Opprett side" for hver side du trenger. Kortkoden legges inn automatisk.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Velge eksisterende side:</strong> Du kan også velge en eksisterende WordPress-side fra dropdown-menyen og tilordne den til en funksjon. Lim inn kortkoden manuelt. Koden kan du kopiere fra ikonet <i class="ka-icon icon-code-simple-solid-full" style="height: 14px;"></i> ved siden av navnet.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <p><strong><?php esc_html_e('Viktig:', 'kursagenten'); ?></strong></p>
                    <ul>
                        <li><?php esc_html_e('Sidene blir merket med "Kursagenten" i sideoversikten for enkel identifikasjon.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Du kan fritt endre tittel, slug og innhold på sidene. Det eneste som ikke bør fjernes er kortkoden.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Du kan legge til ekstra tekst over eller under kortkoden for introduksjon, SEO-tekst eller annen informasjon.', 'kursagenten'); ?></li>
                        <li><?php echo wp_kses_post(__('Designet på listene kan endres med attributter i kortkoden. Se <a href="#lister">oversikt</a> for å se hvilke valg du kan gjøre.', 'kursagenten')); ?></li>
                    </ul>
                </div>

                <div id="utseende-lister" class="options-card">
                    <h3><?php esc_html_e('Styre utseende på kurslister', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('Under <a href="admin.php?page=design">Kursdesign</a> styrer du listedesign på systemsidene. Det finnes innstillinger for bildebruk, antall kolonner og paginering.', 'kursagenten')); ?></p>
                    <h4><?php esc_html_e('Kursliste med filter', 'kursagenten'); ?></h4>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Listedesign:</strong> Velg listedesign (standard, rutenett, kompakt, enkel liste, enkle kort).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Vis bilder:</strong> Skru av/på bilder i listen. Best egnet om du har lastet opp bilder til kursene i Kursagenten.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Antall per side:</strong> Velg antall kurs som skal vises per side.', 'kursagenten')); ?></li>
                    </ul>
                    <h4><?php esc_html_e('Taksonomisider', 'kursagenten'); ?></h4>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Bredde:</strong> Velg om du vil bruke temaets standardbredde eller full bredde. Stort sett vil standardbredde fungerer som tiltenkt.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Layout:</strong> Velg designet på siden. Dette styrer hvor/hvordan tittel, tekst, hovedbilde og kursliste vises.', 'kursagenten')); ?></li>
                        
                        <li><?php echo wp_kses_post(__('<strong>Listedesign:</strong> Velg designet på kurslisten (standard, rutenett, kompakt, enkle kort...)', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Visningstype:</strong> Velg om du vil vise hovedkurs (ett kurs per rad/boks) eller alle kursdatoer (tilsvarende "Kursliste med filter").', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Vis bilder:</strong> Skru av/på bilder i listen. Best egnet om du har lastet opp bilder til kursene i Kursagenten.', 'kursagenten')); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('Disse innstillingene fungerer som standardvalg for kortkodene. Du kan også overstyre dem direkte i kortkoden med attributter. Se <a href="#lister">Kortkoder for lister og grid</a> for mer informasjon.', 'kursagenten')); ?></p>
                </div>

                <div class="options-card">
                    <h3><?php esc_html_e('Filtre – slik fungerer de', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('Kurslisten kan filtreres på kategori, sted, startmåned, språk m.m. Filtrene vises som del av kortkoden <code class="copytext">[kursliste]</code>. Har du opprettet sider via <a href="admin.php?page=design">Kursdesign</a> → Wordpress sider, gjelder dette siden "Kurs".', 'kursagenten')); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Plassering:</strong> Filtrene velges ved å dra dem til korrekt plassering. Du kan velge å vise dem til venstre for kurslisten og/eller over kurslisten.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Tagger eller lister:</strong> Filtrene kan velges som tagger eller avkrysningsliste. Tagger er knapper som velger ett filter av gangen, og avkrysningsliste er en liste som kan velge flere filter samtidig.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Rekkefølge:</strong> Du kan også dra i filtrene for å endre rekkefølgen de vises i.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Visning av liste:</strong> I venstre kolonne vil listene vises som avkrysningsliste, mens over kursliten vises lister som dropdown-menyer.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Test:</strong> Besøk frontend og se at det ser ut som det skal.', 'kursagenten')); ?></li>
                    </ul>
                </div>

                <div id="gutenberg-blokker" class="options-card">
                    <h3><?php esc_html_e('Gutenberg blokker', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('Taksonomiene <strong>Kurskategorier</strong>, <strong>Kurssteder</strong> og <strong>Instruktører</strong> er tilgjengelige som egne Gutenberg-blokker i blokkeditoren.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('Du kan bruke blokkene i stedet for kortkoder hvis du bygger sidene med Gutenberg. De samme valgene som beskrives under <a href="#lister">Kortkoder for lister og grid</a> er i stor grad tilgjengelige direkte i blokkinnstillingene.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Når bør du bruke kortkoder?</strong> Hvis du bygger sidene med Elementor eller andre page builders, vil kortkoder sannsynligvis være det du må bruke.', 'kursagenten')); ?></p>
                </div>

                <div id="kortkoder" class="options-card">
                    <h3><?php esc_html_e('Kortkoder', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('<strong>Hva er kortkoder?</strong> Kortkoder er små koder du plasserer i innhold (sider/innlegg) for å vise dynamiske lister og komponenter fra pluginen.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Hvor brukes de?</strong> Vanligvis på egne sider eller innlegg (f.eks. «Kurs», «Kurskategorier», «Kurssteder», «Instruktører»). Du kan også bruke dem i menyen eller widgets.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Eget innhold over/under:</strong> Du kan legge inn vanlig innhold i editoren over og under kortkoden for å skrive en introduksjon, legge til bilder eller annen informasjon.', 'kursagenten')); ?></p>
                    <div class="ka-grid ka-grid-3">
                    
                        <div class="kort">
                            <h4><?php echo wp_kses_post(__('Design: Lister og grid <span class="small"><a href="#lister">mer info</a></span>', 'kursagenten')); ?></h4>
                            <p><span class="copytext">[kurskategorier]</span><br><span class="copytext">[kurssteder]</span><br><span class="copytext">[instruktorer]</span></p>
                        
                            <h4><?php echo wp_kses_post(__('Kursliste med filter <span class="small"><a href="#lister">mer info</a></span>', 'kursagenten')); ?></h4>
                            <p><span class="copytext">[kursliste]</span></p>
                            
                            <h4><?php esc_html_e('Bilder', 'kursagenten'); ?></h4>
                            <p><span class="copytext">[plassholderbilde-kurs]</span><br><span class="copytext">[plassholderbilde-generelt]</span><br><span class="copytext">[plassholderbilde-instruktor]</span><br><span class="copytext">[plassholderbilde-sted]</span></p>
                            
                        </div>
                        <div class="kort">
                            <h4><?php esc_html_e('Bedriftsinformasjon', 'kursagenten'); ?></h4>
                            <p><span class="copytext">[firmanavn]</span><br><span class="copytext">[adresse]</span><br><span class="copytext">[postnummer]</span><br><span class="copytext">[poststed]</span><br><span class="copytext">[hovedkontakt]</span><br><span class="copytext">[epost]</span><br><span class="copytext">[telefon]</span><br><span class="copytext">[infotekst]</span><br><span class="copytext">[facebook]</span><br><span class="copytext">[instagram]</span><br><span class="copytext">[linkedin]</span><br><span class="copytext">[youtube]</span></p>
                        </div>
                        <div class="kort">
                        <h4><?php esc_html_e('Meny', 'kursagenten'); ?></h4>
                        <p><span class="copytext">[ka-meny type="kurskategorier"]</span><br><span class="copytext">[ka-meny type="kurskategorier" start="din-hovedterm" st="sted/st=ikke-sted"]</span><br><span class="copytext">[ka-meny type="instruktorer"]</span><br><span class="copytext">[ka-meny type="kurssteder"]</span></p>
                        <p title="<?php echo esc_attr(__('Legg inn kortkoden i tekstfeltet i en egendefinert meny. I url-feltet skriver du #. For å få menyen som en undermeny, dra dette menypunktet til høyre innunder et annet menypunkt. For å lage meny av en bestemt kategori (med underkategorier), skriv inn kategori-slug etter start="".', 'kursagenten')); ?>"><img src="<?php echo esc_url(plugins_url('assets/images/admin-menu-illustration.jpg', KURSAG_PLUGIN_FILE)); ?>" alt="<?php echo esc_attr(__('Kursagenten admin', 'kursagenten')); ?>" style="width: 100%; max-width: 400px;"></p>
                        </div>
                        
                        
                    </div>
                </div>

                <div class="options-card">
                    <h3 id="lister"><?php esc_html_e('Kortkoder for lister og grid', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('Kortkoder kan legges inn i teksten på sider og blogginnlegg. Du kan legge inn hele kurslisten, eller lister med enten alle kurskategorier, kurs i samme kategori (brukes på kurssider), eller instruktører.<br>Det er mange ulike valg. Du finner full kortkode under, med samtlige valg, samt en liste som forklarer alle valgene.<br>Kortkoden kopieres, og limes inn der du ønsker å vise den. <br>Merk at du må fjerne eventuelle valg du ikke trenger, og deler der flere valg er listet opp (feks som stablet/rad/liste).', 'kursagenten')); ?></p>
                    <div class="kort" style="background: #fbfbfb; padding: 1em; border-radius: 10px;">
                        <p><?php echo wp_kses_post(__('<strong>Kursliste med filter </strong><span class="smal"><span class="copytext">[kursliste]</span></span><br><span class="copytext small">[kursliste kategori="web" sted="oslo" måned="9" språk="norsk" list_type="grid" filter="topp" knapper="signup_link" bilder="yes" vis="-sluttdato" st=sted/st=ikke-sted klasse="min-klasse"]</span>', 'kursagenten')); ?></p>
                        <p><?php echo wp_kses_post(__('<strong>Liste med kurskategorier </strong><span class="smal"><span class="copytext">[kurskategorier]</span></span><br><span class="copytext small" style="color:#666">[kurskategorier kilde="bilde/ikon" layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1  radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13" fontmaks="18" avstand="2em .5em" skygge="ja" vis="hovedkategorier/subkategorier/slug/standard" st=sted/st=ikke-sted utdrag="ja" klasse="min-klasse"]</span>', 'kursagenten')); ?></p>
                        <p><?php echo wp_kses_post(__('<strong>Liste med kurssteder </strong><span class="smal"><span class="copytext">[kurssteder]</span></span><br><span class="copytext small" style="color:#666">[kurssteder layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" utdrag="ja" vis="standard/alta,oslo,bergen" region="østlandet" stedinfo="ja" klasse="min-klasse"]</span>', 'kursagenten')); ?></p>
                        <p><?php echo wp_kses_post(__('<strong>Liste med instruktører </strong><span class="smal"><span class="copytext">[instruktorer]</span></span><br><span class="copytext small" style="color:#666">[instruktorer layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" skjul="Iris,Anna" utdrag="ja" beskrivelse="ja" klasse="min-klasse"]</span>', 'kursagenten')); ?></p>
                    </div>
                    <p>&nbsp;</p>
                    
                    <div class="">
                        <h3><?php esc_html_e('Valg for lister og grid', 'kursagenten'); ?></h3>
                        <p><?php echo wp_kses_post(__('Skriver du kun kortkodene <span class="copytext">[kurskategorier]</span>, <span class="copytext">[kurssteder]</span> eller <span class="copytext">[instruktorer]</span> brukes standardvalgene.<br>Bruk eventuelt de fulle kodene over, og fjern de valgene du ikke trenger.', 'kursagenten')); ?></p>
                    
                        <table class="widefat light-grey-rows" style="border: 0; background: #fbfbfb; padding: 1em; border-radius: 10px;">
                            <colgroup>
                                <col style="width:10%;">
                                <col style="width:40%;">
                                <col style="width:20%;">
                                <col style="width:11%;">
                                <col style="width:19%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Valg', 'kursagenten'); ?></th>
                                    <th><?php esc_html_e('Beskrivelse', 'kursagenten'); ?></th>
                                    <th><?php esc_html_e('Variant', 'kursagenten'); ?></th>
                                    <th><?php esc_html_e('Kan brukes på', 'kursagenten'); ?></th>
                                    <th><?php esc_html_e('Eksempel', 'kursagenten'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php esc_html_e('Kilde (k)', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Du kan velge om du vil bruke hovedbilde, eller laste opp egne ikoner for lister. Velg kilde=ikon hvis du vil bruke disse.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('bilde, ikon<br><strong>Standard:</strong> bilde', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurskategorier', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">kilde=ikon</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Kilde (i)', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Du kan velge om du vil bruke et bilde du laster opp selv, eller bruke bildet hentet fra Kursagenten. Velg kilde=ka-bilde hvis du vil bruke bilde fra Kursagenten.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('bilde, ka-bilde<br><strong>Standard:</strong> bilde', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Instruktører', 'kursagenten'); ?></td>
                                    <td>[instruktorer <span class="copytext">kilde=ka-bilde</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Layout', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Ulike layout. Stablet viser bilde over kurs-/kategorinavn, rad viser bilde til venstre. Liste viser alle navn under hverandre. Liste har lavere mellomrom mellom punktene, og passer bedre med små bilder/uten bilder.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('stablet, rad, liste<br><strong>Standard:</strong> stablet', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">layout=rad</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('List_type', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('Velg listedesign for kortkoden <span class="copytext">[kursliste]</span> uten å endre global innstilling i Kursdesign.', 'kursagenten')); ?></td>
                                    <td><?php echo wp_kses_post(__('standard, grid, compact, plain, date-and-title, simple-cards<br><strong>Standard:</strong> hentes fra Kursdesign', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kursliste', 'kursagenten'); ?></td>
                                    <td>[kursliste <span class="copytext">list_type="grid"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Bilder', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('Overstyr visning av bilder i <span class="copytext">[kursliste]</span>.', 'kursagenten')); ?></td>
                                    <td><?php echo wp_kses_post(__('yes, no<br><strong>Standard:</strong> hentes fra Kursdesign', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kursliste', 'kursagenten'); ?></td>
                                    <td>[kursliste <span class="copytext">bilder="no"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Filter', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('Overstyr visning av filtre for <span class="copytext">[kursliste]</span>. Nyttig når samme liste skal brukes i ulike sideoppsett.', 'kursagenten')); ?></td>
                                    <td><?php echo wp_kses_post(__('venstre, topp, filter-knapp, skjul<br><strong>Standard:</strong> slik filtere er satt opp i Kursdesign', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kursliste', 'kursagenten'); ?></td>
                                    <td>[kursliste <span class="copytext">filter="skjul"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Knapper', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Velg om kurslisten skal vise vanlig knappestil eller kun påmeldingslink-stil.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('show_buttons, signup_link<br><strong>Standard:</strong> hentes fra Kursdesign', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kursliste', 'kursagenten'); ?></td>
                                    <td>[kursliste <span class="copytext">knapper="signup_link"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Vis', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('Overstyr hvilke detaljer som vises i hvert kurs i <span class="copytext">[kursliste]</span>. Uten prefiks settes kun oppgitte felter til synlig. Med <span class="copytext">-</span>/<span class="copytext">!</span> skjules felt, og med <span class="copytext">+</span> vises felt uten å endre resten.', 'kursagenten')); ?></td>
                                    <td><?php echo wp_kses_post(__('tid, varighet, pris, sted, fritekst-sted, rom, instruktør, sluttdato, påmeldingsfrist<br><strong>Standard:</strong> hentes fra Kursdesign', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kursliste', 'kursagenten'); ?></td>
                                    <td>[kursliste <span class="copytext">vis="-sluttdato"</span>]<br>[kursliste <span class="copytext">vis="tid,pris"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Stil', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Vis som kort, med hvit bakgrunn og skygge bak hele kortet.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('kort<br><strong>Standard</strong>: ikke kort', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">stil=kort</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Grid', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Antall kolonner på desktop.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 3 kolonner', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">grid=4</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Gridtablet', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Antall kolonner på tablet.', 'kursagenten'); ?>&nbsp;</td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 2 kolonner', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">gridtablet=2</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Gridmobil', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Antall kolonner på mobil.', 'kursagenten'); ?>&nbsp;</td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 1 kolonne', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">gridmobil=2</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Bildestr', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Skriv inn størrelse på bildet du ønsker, i pixler. Ønsker du ikke bilde, skriv 0. Når bildestr settes til 0, lastes ikke bildene inn i det hele tatt.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 100px', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">bildestr=80px</span>]<br>[kurskategorier <span class="copytext">bildestr=0</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Radavstand', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Skriv inn avstanden mellom radene, i pixler, em eller rem. Ønsker du ingen avstand, skriv 0.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 1rem', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">radavstand=10px</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Avstand', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Avstand rundt alle elementene, første verdi er topp og bunn, andre verdi er venstre og høyre.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('<strong>Standard:</strong> 2em .5em', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">avstand="1em 0"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Bildeform', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Velg helt firkantede bilder, litt avrundet i kantene, eller runde bilder.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('avrundet, rund, firkantet<br><strong>Standard</strong>: avrundet', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">bildeform=rund</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Bildeformat', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Hvorvidt bildet skal være liggende, stående eller kvadratisk.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('feks 4/3, 16/9, 1/1<br><strong>Standard</strong>: 4/3', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">bildeformat=16/9</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Skygge', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Skygge ved musepeker over kurs/kurskategori.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('ja<br><strong>Standard</strong>: uten skygge', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">skygge=ja</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Overskrift', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Velg hvilken overskrift du vil bruke for navnene.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('feks h3, h4, h5, p, span, div<br><strong>Standard</strong>: h3', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">overskrift=h4</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Fontmin', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Teksten justerer seg etter skjermstørrelse. Dette er den minste fontstørrelsen du vil bruke for tekst og overskrifter.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('feks 13px, 15px, 18px<br><strong>Standard</strong>: 13px', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">fontmin=15px</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Fontmaks', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Dette er den største fontstørrelsen du vil bruke for tekst og overskrifter.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('feks 15px, 18px, 26px<br><strong>Standard</strong>: 18px', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">fontmaks=18px</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Vis (k)', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('For de kategoriene som har flere nivåer, er det mulighet til å vise kun toppnivåene, kun underkategoriene, kun underkategorier under gitt foreldreslug, eller alle.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('hovedkategorier, subkategorier, foreldreslug (feks dans eller truck)<br><strong>Standard</strong>: viser alle', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurskategorier', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">vis=hovedkategorier/subkategorier/slug</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Vis (i)', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Velg å vise fornavn, etternavn eller begge.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('fornavn, etternavn<br><strong>Standard</strong>: viser fullt navn', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Instruktører', 'kursagenten'); ?></td>
                                    <td>[instruktorer <span class="copytext">vis=fornavn</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Vis (s)', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Filtrer stedslisten til kun vise spesifikke steder. Kan bruke stedsnavn eller slug (case-insensitive).', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('standard, kommaseparert liste (feks alta,oslo,bergen eller "Oslo,Mo i Rana")<br><strong>Standard</strong>: viser alle steder', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurssteder', 'kursagenten'); ?></td>
                                    <td>[kurssteder <span class="copytext">vis=alta,oslo,bergen</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Region', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Filtrer kurssteder basert på region (kun hvis regioner er aktivert). Kan kombineres med vis-parameteren (OR-logikk: viser steder fra regionen ELLER de spesifiserte stedene).', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('sørlandet, østlandet, vestlandet, midt-norge, nord-norge<br><strong>Standard</strong>: ingen filtrering', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurssteder', 'kursagenten'); ?></td>
                                    <td>[kurssteder <span class="copytext">region="østlandet"</span>]<br>[kurssteder <span class="copytext">region="østlandet" vis="bergen"</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Stedinfo', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Vis liste med spesifikke lokasjoner under hvert stedsnavn. Dette kommer fra feltet "Fritekst sted" i Kursagenten.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('ja<br><strong>Standard</strong>: vises ikke', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurssteder', 'kursagenten'); ?></td>
                                    <td>[kurssteder <span class="copytext">stedinfo=ja</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Skjul', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Skjul instruktør ved å skrive en kommaseparert liste med fornavn til instruktør slik det er skrevet i feltet "fornavn"', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('kommasepartert liste<br><strong>Standard</strong>: viser alle', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Instruktører', 'kursagenten'); ?></td>
                                    <td>[instruktor <span class="copytext">skjul=Anna,Per</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('St', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Velg å vise/skjule kurskategorier/menypunkter som hører til spesifikke steder. Eksempel: vis alle kurs som ikke er nettkurs: st="ikke-nettbasert"', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('sted, ikke-sted<br><strong>Standard</strong>: viser alle', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Kurskategorier, automenyer', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">st=sted</span>]<br>[ka-meny type="kurskategorier" <span class="copytext">st=sted</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Utdrag', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Vis tekst fra feltet "Kort beskrivelse".', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('ja<br><strong>Standard</strong>: viser ikke', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurssted <span class="copytext">utdrag=ja</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Beskrivelse', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Vis tekst fra feltet "Utvidet beskrivelse". Merk at dette vil overskrive utdrag.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('ja<br><strong>Standard</strong>: viser ikke', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Instruktører', 'kursagenten'); ?></td>
                                    <td>[instruktorer <span class="copytext">beskrivelse=ja</span>]</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Klasse', 'kursagenten'); ?></td>
                                    <td><?php esc_html_e('Legg til egendefinert CSS-klasse til wrapper-elementet. Nyttig for custom styling eller tema-spesifikke behov.', 'kursagenten'); ?></td>
                                    <td><?php echo wp_kses_post(__('tekst<br><strong>Standard</strong>: tom (ingen klasse)', 'kursagenten')); ?></td>
                                    <td><?php esc_html_e('Alle', 'kursagenten'); ?></td>
                                    <td>[kurskategorier <span class="copytext">klasse="min-egen-klasse"</span>]</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h2><?php esc_html_e('Ekstra innhold', 'kursagenten'); ?></h2>
                
                <div id="menyer" class="options-card">
                    <h3><?php esc_html_e('Menyer', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Kursagenten tilbyr både automenyer og meny-kortkoder, slik at du kan bygge dynamiske menyer som alltid speiler gjeldende kurskategorier, kurssteder og instruktører.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Automenyer i WordPress-menyen', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Automenyer brukes når du vil at menyen automatisk skal vise taksonomier og/eller kurs uten at du manuelt må oppdatere menyelementer når noe endres.', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('Gå til <strong>Utseende → Menyer</strong> i WordPress.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Finn boksen <strong>"Kursagenten automenyer"</strong> i venstre kolonne. Hvis du ikke ser den, sjekk at den er aktivert under "Skjerminnstillinger" øverst.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Velg ønsket automeny (f.eks. <strong>Kurskategorier</strong>, <strong>Kategorier og kurs</strong>, <strong>Kurssteder</strong> eller <strong>Instruktører</strong>) og klikk <strong>Legg til i meny</strong>.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('Dra automenyen til riktig plassering i menystrukturen. Som hovedmenypunkt kan den stå på toppnivå, eller du kan dra den innunder en eksisterende side (f.eks. "Kurs") for å bruke den som undermeny.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Klikk på trekanten på menypunktet for å åpne innstillingene. Her kan du blant annet:', 'kursagenten'); ?>
                            <ul>
                                <li><?php echo wp_kses_post(__('Velge om menyen skal vise <strong>hovedkategorier</strong>, <strong>subkategorier</strong> eller starte i en bestemt kategori.', 'kursagenten')); ?></li>
                                <li><?php echo wp_kses_post(__('Begrense innholdet med <strong>stedsfilter</strong> (f.eks. bare "Oslo" eller "ikke-Oslo").', 'kursagenten')); ?></li>
                                <li><?php esc_html_e('For "Kategorier og kurs": velge om menyen bare skal vise kurs (uten kategorinivå).', 'kursagenten'); ?></li>
                            </ul>
                        </li>
                        <li><?php esc_html_e('Lagre menyen som vanlig. På forsiden vil automenyen automatisk utvides til riktig trestruktur basert på taksonomier og kurs i Kursagenten.', 'kursagenten'); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Meny-kortkoder', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Meny-kortkoder brukes når du vil bygge menyer ved hjelp av kortkoden <code class="copytext">[ka-meny]</code>, typisk i en egendefinert meny eller i innhold der temaet støtter kortkoder.', 'kursagenten')); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('Opprett først en <strong>egendefinert meny</strong> eller et menypunkt av typen "Egendefinert lenke".', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('I <strong>Link-tekst</strong> (eller tilsvarende felt) limer du inn ønsket <code>[ka-meny]</code>-kortkode, f.eks. <code>[ka-meny type="kurskategorier"]</code>.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('I URL-feltet skriver du <code>#</code> (slik at menypunktet kan lagres uten faktisk lenke).', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Dra menypunktet dit menyen skal vises, for eksempel som undermeny under siden "Kurs".', 'kursagenten'); ?></li>
                        <li><?php echo wp_kses_post(__('Bruk parameterne <code>type</code>, <code>start</code> og eventuelt <code>st</code> for å styre hvilke kategorier/steder eller instruktører som skal vises.', 'kursagenten')); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('For flere eksempler på meny-kortkoder og hvordan du kan kombinere dem med andre kortkoder, se seksjonen <a href="#kortkoder">Kortkoder</a> lenger opp på denne siden.', 'kursagenten')); ?></p>
                </div>

                <div id="berike-taksonomisider" class="options-card">
                    <h3><?php esc_html_e('Berike taksonomisider', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Alle taksonomisider (kategorier, kurssteder og instruktører) kan berikes med ekstra innhold som bilder, tekst og beskrivelser. Dette gjør at du kan tilpasse hver enkelt side med unikt innhold utover det som kommer fra Kursagenten.', 'kursagenten'); ?></p>
                    <p><?php esc_html_e('Designmalene viser frem innholdet på forskjellige måter.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Hvordan legge til innhold', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Gå til oversikten over taksonomiene (f.eks. <a href="edit-tags.php?taxonomy=ka_coursecategory&post_type=ka_course">Kurskategorier</a>, <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Kurssteder</a> eller <a href="edit-tags.php?taxonomy=ka_instructors&post_type=ka_course">Instruktører</a>) og klikk på "Rediger" på den taksonomien du vil berike. Her kan du legge til:', 'kursagenten')); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Bilde:</strong> Last opp et hovedbilde som vises på taksonomisiden og i lister.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister og oversikter. Vises ofte direkte under overskriften.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Utvidet beskrivelse:</strong> En lengre tekst med rik tekstformatering (HTML) som vises på taksonomisiden. Her kan du beskrive kategorien, stedet eller instruktøren med tekst, bilder og annen informasjon.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Ekstra felter:</strong> Spesifikke felter avhengig av taksonomitypen (se nedenfor).', 'kursagenten')); ?></li>
                    </ul>
                </div>

                <div id="kurskategorier-innhold" class="options-card">
                    <h3><?php esc_html_e('Kurskategorier', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Kurskategorier kan berikes med bilder, ikoner og beskrivelser. Du kan også kontrollere hvor kategoriene vises.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Tilgjengelige felter', 'kursagenten'); ?></h4>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Navn:</strong> Kategorinavnet (kommer fra Kursagenten, bør ikke endres).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Slug:</strong> URL-vennlig versjon av navnet (bør ikke endres da det kan føre til ødelagte lenker).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Hovedbilde:</strong> Et bilde som vises på kategorisiden og i lister.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Ikon:</strong> Et alternativt bilde som kan brukes i stedet for hovedbilde i lister. Her er det fint å laste opp png-ikoner (bruk <code>kilde=ikon</code> i kortkoden).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister og oversikter. Vises ofte direkte under overskriften.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på kategorisiden.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Synlighet og skjuling', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du kan kontrollere hvor kategoriene vises med flere synlighetsinnstillinger:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Skjul i oversiktslister:</strong> Når aktivert, skjules kategorien i kortkoder som <code>[kurskategorier]</code> og lignende lister. Kategorien vil fortsatt være tilgjengelig direkte via URL.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Skjul i automenyer:</strong> Når aktivert, skjules kategorien i autogenererte menyer som bruker kortkoden <code>[ka-meny]</code>. Dette er nyttig hvis du har kategorier som ikke skal vises i hovedmenyen.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kursliste (kun for kurskategorier):</strong> Tre alternativer styrer hvordan kategorien påvirker kurslisten:', 'kursagenten')); ?>
                            <ul>
                                <li><?php echo wp_kses_post(__('<em>Vis</em> – kategorien vises i filteret og kursene vises i listen (standard).', 'kursagenten')); ?></li>
                                <li><?php echo wp_kses_post(__('<em>Skjul kun kategorien fra filter</em> – kategorien skjules fra filtervalgene, men kurs som er tagget med kategorien vises fortsatt i kurslisten under sine andre kategorier. Nyttig for «brede» tagger som ikke gir mening som filter (f.eks. «Foredrag», «HMS og sikkerhetskurs»).', 'kursagenten')); ?></li>
                                <li><?php echo wp_kses_post(__('<em>Skjul kategorien og alle tilhørende kurs</em> – både kategorien og alle kurs som er tagget med kategorien skjules helt fra kurslisten. Nyttig for interne kategorier som ikke skal være synlige for besøkende.', 'kursagenten')); ?></li>
                            </ul>
                        </li>
                    </ul>
                    <p><?php esc_html_e('Disse innstillingene kan settes både ved redigering av kategorien, via hurtigredigering, og via masseredigering («Kursagenten: Masserediger synlighet») i oversikten.', 'kursagenten'); ?></p>
                </div>

                <div id="kurssteder-innhold" class="options-card">
                    <h3><?php esc_html_e('Kurssteder', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Kurssteder kan berikes med bilder og beskrivelser. Du kan også endre navn på steder og organisere dem i regioner.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Tilgjengelige felter', 'kursagenten'); ?></h4>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Navn:</strong> Stedsnavnet (kommer fra Kursagenten, men kan endres via navnendring-funksjonen).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Slug:</strong> URL-vennlig versjon av navnet (oppdateres automatisk ved navnendring).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Bilde:</strong> Et bilde som vises på stedssiden og i lister.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister. Vises ofte direkte under overskriften.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på stedssiden.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Region:</strong> (Hvis regioner er aktivert) Velg hvilken region stedet tilhører.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Navnendring', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Du kan endre navn på kurssteder som kommer fra Kursagenten. Dette gjøres via <a href="admin.php?page=kursinnstillinger#location-name-mapping">Synkronisering → Navnendring på kurssteder</a>. Se <a href="#stedsnavn-og-regioner">Stedsnavn og regioner</a> for mer informasjon.', 'kursagenten')); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Viktig:</strong> Navnet på kursstedet i WordPress-administrasjonen er skrivebeskyttet når det kommer fra Kursagenten. For å endre navnet som vises på nettsiden, bruk navnendring-funksjonen i stedet for å redigere taksonomien direkte.', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Regioner', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Hvis regioner er aktivert, kan du tilordne hvert kurssted til en region (Sørlandet, Østlandet, Vestlandet, Midt-Norge, Nord-Norge). Dette kan gjøres på to måter:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Automatisk:</strong> Steder blir automatisk tilordnet til regioner basert på fylke når regioner er aktivert.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Manuelt:</strong> Du kan endre region for individuelle steder ved å redigere kursstedet i <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Kurssteder</a>-oversikten.', 'kursagenten')); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('Se <a href="#stedsnavn-og-regioner">Stedsnavn og regioner</a> for mer informasjon om hvordan regioner fungerer.', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Synlighet', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Kurssteder har samme synlighetsinnstillinger som kurskategorier (skjul i oversiktslister og i automenyer).', 'kursagenten'); ?></p>
                </div>

                <div id="instruktorer-innhold" class="options-card">
                    <h3><?php esc_html_e('Instruktører', 'kursagenten'); ?></h3>
                    <p><?php esc_html_e('Instruktører kan berikes med bilder, kontaktinformasjon og beskrivelser. Du kan også opprette egne instruktører som ikke kommer fra Kursagenten, og overskrive data fra Kursagenten.', 'kursagenten'); ?></p>
                    
                    <h4><?php esc_html_e('Tilgjengelige felter', 'kursagenten'); ?></h4>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Navn:</strong> Instruktørens navn (kan deles opp i fornavn og etternavn).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Profilbilde:</strong> Et hovedbilde som vises på instruktørsiden og i lister. Dette kan være fra Kursagenten eller et eget opplastet bilde.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Alternativt bilde:</strong> Et ekstra bilde som kan brukes på instruktørsiden.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>E-post:</strong> Instruktørens e-postadresse.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Telefon:</strong> Instruktørens telefonnummer.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på instruktørsiden.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Overskrive data fra Kursagenten', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('For instruktører som kommer fra Kursagenten, kan du overskrive dataene med egne verdier:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Overstyr profilbilde:</strong> Aktiver denne for å bruke et eget opplastet bilde i stedet for bildet fra Kursagenten.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Overstyr profil fra Kursagenten:</strong> Aktiver denne for å kunne redigere navn, e-post og telefon. Når aktivert, vil ikke disse feltene oppdateres automatisk fra Kursagenten ved synkronisering.', 'kursagenten')); ?></li>
                    </ul>
                    <p><?php echo wp_kses_post(__('<strong>Viktig:</strong> Når du overskriver felter, vil de ikke lenger oppdateres automatisk fra Kursagenten. Du må manuelt oppdatere dem hvis det er endringer i Kursagenten.', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Opprette egne instruktører', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du kan opprette instruktører direkte i WordPress som ikke kommer fra Kursagenten:', 'kursagenten'); ?></p>
                    <ol>
                        <li><?php echo wp_kses_post(__('Gå til <a href="edit-tags.php?taxonomy=ka_instructors&post_type=ka_course">Instruktører</a>-oversikten.', 'kursagenten')); ?></li>
                        <li><?php esc_html_e('Klikk på "Legg til ny instruktør".', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Fyll ut navn og eventuelt slug.', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Klikk "Legg til ny instruktør".', 'kursagenten'); ?></li>
                        <li><?php esc_html_e('Rediger instruktøren for å legge til bilder, kontaktinformasjon og beskrivelser.', 'kursagenten'); ?></li>
                    </ol>
                    <p><?php esc_html_e('Egne instruktører fungerer på samme måte som instruktører fra Kursagenten, men de vil ikke oppdateres automatisk og kan fritt redigeres uten å aktivere "overstyr"-funksjoner.', 'kursagenten'); ?></p>
                    <h4><?php esc_html_e('Vise fullt navn, fornavn eller etternavn', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du kan velge å vise fullt navn, fornavn eller etternavn på instruktørsiden og i listen med instruktører. Du må både endre innstillinger i Kursdesign og i Wordpress-siden som viser instruktørene.', 'kursagenten'); ?></p>
                        <ol>
                            <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=design#design-taksonomi">Kursdesign → Taksonomisider</a>', 'kursagenten')); ?></li>
                            <li><?php esc_html_e('Klikk på "Egne innstillinger for instruktører"', 'kursagenten'); ?></li>
                            <li><?php esc_html_e('Velg "Fullt navn", "Fornavn" eller "Etternavn" i feltet "Navnevisning".', 'kursagenten'); ?></li>
                            <li><?php esc_html_e('Klikk "Lagre".', 'kursagenten'); ?></li>
                            <li><?php echo wp_kses_post(__('Gå til <a href="admin.php?page=design#section-systemsider">Wordpress sider</a>. Rediger siden med instruktøroversikten og legg til vis="fornavn" eller vis="etternavn" i kortkoden for å vise kun fornavn eller etternavn. Du kan gå direkte til redigering fra <a href="admin.php?page=design#section-systemsider">Wordpress sider</a>.', 'kursagenten')); ?></li>
                        </ol>
                    <h4><?php esc_html_e('Synlighet', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Instruktører har samme synlighetsinnstillinger som kurskategorier (skjul i oversiktslister og i automenyer). Du kan også skjule spesifikke instruktører i kortkoder ved å bruke <code>skjul</code>-parameteren i kortkoden <code>[instruktorer]</code>.', 'kursagenten')); ?></p>
                </div>

                <h2><?php esc_html_e('Design', 'kursagenten'); ?></h2>

                <div id="designmaler-kurs" class="options-card">
                    <h3><?php esc_html_e('Designmaler for kurs', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('I <a href="admin.php?page=design#section-enkeltkurs">Kursdesign → Enkeltkurs</a> velger du designmal for enkeltsider for kurs. Designmalen påvirker hele oppsettet av kursdetaljsiden:', 'kursagenten')); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Layout og struktur:</strong> Hvor elementene plasseres på siden (header, innhold, sidekolonne, footer).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Rekkefølge på elementer:</strong> Hvilken rekkefølge informasjonen vises i (introtekst, hovedinnhold, kursdatoer, instruktører, osv.).', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Visuelle detaljer:</strong> Styling, spacing og annet visuelt design.', 'kursagenten')); ?></li>
                    </ul>
                    <p><?php esc_html_e('Ved å bytte designmal endres hele presentasjonen av kurset. Dette kan påvirke hvordan besøkende opplever kursinformasjonen.', 'kursagenten'); ?></p>
                    <p><?php esc_html_e('Det er foreløpig ikke mange designmaler tilgjengelig for enkeltkurs. Vi planlegger å utvide med flere designmaler snart.', 'kursagenten'); ?></p>
                    <p><?php echo wp_kses_post(__('<strong>Viktig:</strong> Endringer i designmal kan kreve oppdatering/refresh av cache og permalenker, spesielt ved utstrakte URL-tilpasninger.', 'kursagenten')); ?></p>
                </div>

                <div id="design-taksonomi" class="options-card">
                    <h3><?php esc_html_e('Design på taksonomi', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('I <a href="admin.php?page=design">Kursdesign</a> kan du velge designmaler og innstillinger for taksonomisider (kategorier, kurssteder og instruktører).', 'kursagenten')); ?></p>
                    
                    <h4><?php esc_html_e('Designmaler', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Du kan velge en felles designmal for alle taksonomier, eller aktivere separate designmaler for hver taksonomitype:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Felles designmal:</strong> Alle taksonomier bruker samme designmal.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Separate designmaler:</strong> Aktiver dette for å kunne velge forskjellige designmaler for kategorier, steder og instruktører.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Listetype', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Velg hvordan kurslistene på taksonomisidene skal vises:', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Standard:</strong> En tradisjonell listevisning med kurskort, ett kurs per rad/boks.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Grid:</strong> Et rutenett med kurskort i flere kolonner. Mulig å velge antall kolonner for desktop, tablet og mobil.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Kompakt:</strong> En mer kompakt listevisning med mindre mellomrom. Uten bakgrunn, og med færre kurselementer', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Ren og enkel liste:</strong> Basert på Standard liste, men uten bakgrunn, og med færre kurselementer', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Enkle kort:</strong> Hvite kort med overskrift og tekst. Viser neste tilgjengelige dato for kursene. Mulig å velge antall kolonner for desktop, tablet og mobil.', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Layout', 'kursagenten'); ?></h4>
                    <p><?php esc_html_e('Velg designet på siden. Dette styrer hvor/hvordan tittel, tekst, hovedbilde og kursliste vises.', 'kursagenten'); ?></p>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Standard - med bilde og beskrivelse:</strong> Tittel og beskrivelse, deretter bilde og beskrivelse, deretter kursliste', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Enkel - kun tittel og kort beskrivelse:</strong> Tittel og kort beskrivelse, deretter kursliste. Egnet når du ikke har bilder eller utvidet beskrivelse.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Profil - rundt bilde og tittel:</strong> Rundt bilde, deretter tittel og beskrivelse, deretter kursliste', 'kursagenten')); ?></li>
                    </ul>
                    
                    <h4><?php esc_html_e('Innstillinger for kursliste', 'kursagenten'); ?></h4>
                    <p><?php echo wp_kses_post(__('Du kan også konfigurere hvordan kurslistene på taksonomisidene skal vises. Se <a href="#section-styre-utseende-på-kurslister">Styre utseende på kurslister</a> for mer informasjon.', 'kursagenten')); ?></p>
                </div>

                <div id="hooks" class="options-card">
                    <h3><?php esc_html_e('Hooks', 'kursagenten'); ?></h3>
                    <div class="ka-grid ka-grid-3">
                        <div class="kort">
                                <h4><?php esc_html_e('Taksonomi-sider', 'kursagenten'); ?></h4>
                                <p style="line-height: 1.8;">
                                    <strong><?php esc_html_e('Header før', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_header_before</span><br><span style="color:#777;font-style:italic"> – <?php echo wp_kses_post(__('Før hele header-seksjonen (før &lt;article&gt;).', 'kursagenten')); ?></span><br>
                                    <strong><?php esc_html_e('Header etter tittel', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_after_title</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Vises rett etter H1 i toppseksjonen.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Header etter seksjon', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_header_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Vises rett under hele header-blokken.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Venstre kolonne', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_left_column</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Plassering for innhold i venstre kolonne.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Høyre kolonne topp', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_right_column_top</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Øverst i høyre kolonne.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Høyre kolonne bunn', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_right_column_bottom</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Nederst i høyre kolonne.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Under bilde og beskrivelse', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_below_description</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Like under hovedbilde/utvidet beskrivelse, før kurslisten.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Før kursliste', 'kursagenten'); ?></strong> <span class="copytext">ka_courselist_before</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Under overskrift, over filter/paginering og liste.', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Etter paginering', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_pagination_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Rett under pagineringskontroller i mal "Standard".', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Footer', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_footer</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Helt nederst, etter kurslisten (bunnseksjon).', 'kursagenten'); ?></span><br>
                                    <strong><?php esc_html_e('Etter hele siden', 'kursagenten'); ?></strong> <span class="copytext">ka_taxonomy_after</span><br><span style="color:#777;font-style:italic"> – <?php echo wp_kses_post(__('Etter hele footer-seksjonen (etter &lt;/article&gt;).', 'kursagenten')); ?></span>
                                </p>
                        </div>
                        <div class="kort">
                            <h4><?php esc_html_e('Enkeltkurs', 'kursagenten'); ?></h4>
                            <p style="line-height: 1.8;">
                            <strong><?php esc_html_e('Header før', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_header_before</span><br><span style="color:#777;font-style:italic"> – <?php echo wp_kses_post(__('Før hele header-seksjonen (før &lt;article&gt;).', 'kursagenten')); ?></span><br>
                            <strong><?php esc_html_e('Header etter tittel', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_header_links_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter lenkene i header-seksjonen.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Header etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_header_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Rett under hele header-blokken.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Kursliste etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_courselist_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter eventuell kursliste-seksjon på detaljsiden.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Neste kurs', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_nextcourse_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter modulen "Neste kurs".', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Introtekst før', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_content_intro_before</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Før introtekst.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Introtekst etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_content_intro_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter introtekst.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Hovedinnhold før', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_content_before</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Før hovedinnholdet.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Hovedinnhold etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_content_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter hovedinnholdet.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Sidekolonne før', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_aside_before</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Før sidekolonne/aside.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Sidekolonne etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_aside_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Etter sidekolonne/aside.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Footer før', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_footer_before</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Rett før footer.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Footer etter', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_footer_after</span><br><span style="color:#777;font-style:italic"> – <?php esc_html_e('Rett etter footer-seksjonen.', 'kursagenten'); ?></span><br>
                            <strong><?php esc_html_e('Etter hele siden', 'kursagenten'); ?></strong> <span class="copytext">ka_singel_after</span><br><span style="color:#777;font-style:italic"> – <?php echo wp_kses_post(__('Etter hele footer-seksjonen (etter &lt;/article&gt;).', 'kursagenten')); ?></span><br>
                            </p>
                        </div>

                        <div class="kort">
                            <h4><?php esc_html_e('Annet', 'kursagenten'); ?></h4>
                            <p><?php esc_html_e('Hooks kommer...', 'kursagenten'); ?><br><span class="copytext"></span></p>
                         </div>
                        
                        
                    </div>
                </div>

                <!-- Tilgjengelige ikoner -->
                <?php if (function_exists('kursagenten_icon_overview_shortcode')): ?>
                    <div id="tilgjengelige-ikoner" class="options-card">
                    <h3><?php esc_html_e('Tilgjengelige ikoner', 'kursagenten'); ?></h3>
                    <p><?php echo wp_kses_post(__('Ikoner er tilgjengelige som html med css-klasser. Du kan bruke dem direkte i HTML-kode. Styr størrelse og farge med width, height og background-color på i.ka-icon. Husk å legge til <i>icon-</i> før navnet på ikonet. F.eks. icon-calendar. Eksempel:', 'kursagenten')); ?></p>
                    <pre><code class="copytext">&lt;i class="ka-icon icon-calendar"&gt;&lt;/i&gt;</code></pre> 
                    <style>
                        .ka-wrap i.ka-icon {
                            height: 20px;
                        }
                    </style>

                        <?php echo kursagenten_icon_overview_shortcode(); ?>
                    </div>
                <?php endif; ?>

                <h2><?php esc_html_e('Annet', 'kursagenten'); ?></h2>

                <div class="options-card">
                    <h3><?php esc_html_e('Tips og feilsøking', 'kursagenten'); ?></h3>
                    <ul>
                        <li><?php echo wp_kses_post(__('<strong>Permalenker:</strong> Ved endring av URL-innstillinger, lagre «Permalenker» på nytt.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Cache:</strong> Tøm cache hvis du ikke ser endringer umiddelbart.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Bilder:</strong> Bruk plassholder-bilder via kortkodene om du mangler bilder.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Synkronisering:</strong> Hvis kurs ikke oppdateres automatisk, sjekk at webhooks er konfigurert korrekt i Kursagenten.', 'kursagenten')); ?></li>
                        <li><?php echo wp_kses_post(__('<strong>Designendringer:</strong> Hvis designendringer ikke vises, kan det være nødvendig å tømme cache og oppdatere permalenker.', 'kursagenten')); ?></li>
                    </ul>
                </div>

                    <?php kursagenten_admin_footer(); ?>
        <?php
    }
}

if (is_admin()) {
    new KA_Documentation_Page();
}
