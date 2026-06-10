/**
 * Generates scripts/pl_PL-patch-trans-data.js from EN reference + manual PL map.
 * Run once: node scripts/_gen-pl-trans-data.js
 */
const fs = require('fs');
const path = require('path');

const ref = JSON.parse(
  fs.readFileSync(path.join(__dirname, '..', 'lang', '.pl-translate-with-de.json'), 'utf8')
);

/** @type {Record<string, string>} */
const PL = {
  '(100-1000px)': '(100-1000px)',
  '<strong>Bygg ditt eget design</strong> er aktivt. Du kan fortsatt styre kursdata-visning under.':
    '<strong>Stwórz własny projekt</strong> jest aktywny. Nadal możesz sterować wyświetlaniem danych kursu poniżej.',
  '<strong>Bygg ditt eget design</strong> er aktivt. Kursagenten bruker WordPress sitt malhierarki, slik at tema/page builder kan styre oppsett.':
    '<strong>Stwórz własny projekt</strong> jest aktywny. Kursagenten korzysta z hierarchii szablonów WordPress, dzięki czemu motyw/kreator stron może sterować układem.',
  '<strong>Byggeblokker kommer:</strong> vi planlegger moduler for blant annet kursliste med kurssteder/lenker og informasjon om kommende kurs.':
    '<strong>Bloki wkrótce:</strong> planujemy moduły m.in. listy kursów z lokalizacjami/linkami oraz informacjami o nadchodzących kursach.',
  '<strong>Cache:</strong> Tøm cache hvis du ikke ser endringer umiddelbart.':
    '<strong>Pamięć podręczna:</strong> Wyczyść cache, jeśli nie widzisz zmian od razu.',
  '<strong>Designendringer:</strong> Hvis designendringer ikke vises, kan det være nødvendig å tømme cache og oppdatere permalenker.':
    '<strong>Zmiany projektu:</strong> Jeśli zmiany projektu nie są widoczne, może być konieczne wyczyszczenie cache i odświeżenie bezpośrednich odnośników.',
  '<strong>E-post:</strong> Instruktørens e-postadresse.':
    '<strong>E-mail:</strong> Adres e-mail instruktora.',
  '<strong>Egen opprydding:</strong> Du kan også kjøre opprydding separat ved å klikke på knappen "Rydd opp i kurs" på <a href="admin.php?page=kursinnstillinger">Synkronisering</a>-siden. Dette er nyttig hvis du bare ønsker å rydde opp uten å synkronisere alle kursene på nytt.':
    '<strong>Osobne czyszczenie:</strong> Możesz też uruchomić czyszczenie osobno, klikając przycisk „Uporządkuj kursy” na stronie <a href="admin.php?page=kursinnstillinger">Synchronizacja</a>. Przydatne, gdy chcesz tylko posprzątać bez ponownej synchronizacji wszystkich kursów.',
  '<strong>Eget innhold over/under:</strong> Du kan legge inn vanlig innhold i editoren over og under kortkoden for å skrive en introduksjon, legge til bilder eller annen informasjon.':
    '<strong>Własna treść powyżej/poniżej:</strong> Możesz dodać zwykłą treść w edytorze powyżej i poniżej shortcode, aby napisać wstęp, dodać obrazy lub inne informacje.',
  '<strong>Ekstra felter:</strong> Spesifikke felter avhengig av taksonomitypen (se nedenfor).':
    '<strong>Dodatkowe pola:</strong> Pola specyficzne w zależności od typu taksonomii (patrz poniżej).',
  '<strong>Enkel - kun tittel og kort beskrivelse:</strong> Tittel og kort beskrivelse, deretter kursliste. Egnet når du ikke har bilder eller utvidet beskrivelse.':
    '<strong>Prosty — tylko tytuł i krótki opis:</strong> Tytuł i krótki opis, następnie lista kursów. Odpowiedni, gdy nie masz obrazów ani rozszerzonego opisu.',
  '<strong>Enkle kort:</strong> Hvite kort med overskrift og tekst. Viser neste tilgjengelige dato for kursene. Mulig å velge antall kolonner for desktop, tablet og mobil.':
    '<strong>Proste karty:</strong> Białe karty z nagłówkiem i tekstem. Pokazuje najbliższą dostępną datę kursów. Można wybrać liczbę kolumn na pulpit, tablet i mobile.',
  '<strong>Felles designmal:</strong> Alle taksonomier bruker samme designmal.':
    '<strong>Wspólny szablon projektu:</strong> Wszystkie taksonomie używają tego samego szablonu projektu.',
  '<strong>Grid:</strong> Et rutenett med kurskort i flere kolonner. Mulig å velge antall kolonner for desktop, tablet og mobil.':
    '<strong>Siatka:</strong> Siatka kart kursów w wielu kolumnach. Można wybrać liczbę kolumn na pulpit, tablet i mobile.',
  '<strong>Hovedbilde:</strong> Et bilde som vises på kategorisiden og i lister.':
    '<strong>Główny obraz:</strong> Obraz wyświetlany na stronie kategorii i na listach.',
  '<strong>Hva er kortkoder?</strong> Kortkoder er små koder du plasserer i innhold (sider/innlegg) for å vise dynamiske lister og komponenter fra pluginen.':
    '<strong>Czym są shortcody?</strong> Shortcody to małe kody umieszczane w treści (strony/wpisy), aby wyświetlać dynamiczne listy i komponenty z wtyczki.',
  '<strong>Hvor brukes de?</strong> Vanligvis på egne sider eller innlegg (f.eks. «Kurs», «Kurskategorier», «Kurssteder», «Instruktører»). Du kan også bruke dem i menyen eller widgets.':
    '<strong>Gdzie są używane?</strong> Zwykle na dedykowanych stronach lub wpisach (np. „Kursy”, „Kategorie kursów”, „Lokalizacje kursów”, „Instruktorzy”). Można je też używać w menu lub widżetach.',
  '<strong>Ikon:</strong> Et alternativt bilde som kan brukes i stedet for hovedbilde i lister. Her er det fint å laste opp png-ikoner (bruk <code>kilde=ikon</code> i kortkoden).':
    '<strong>Ikona:</strong> Alternatywny obraz zamiast głównego na listach. Dobrze sprawdzają się ikony PNG (użyj <code>kilde=ikon</code> w shortcode).',
  '<strong>Instruktører</strong> – Inneholder <code class="copytext">[instruktorer]</code> for å vise alle instruktører.':
    '<strong>Instruktorzy</strong> – Zawiera <code class="copytext">[instruktorer]</code> do wyświetlenia wszystkich instruktorów.',
  '<strong>Kompakt:</strong> En mer kompakt listevisning med mindre mellomrom. Uten bakgrunn, og med færre kurselementer':
    '<strong>Kompaktowy:</strong> Bardziej zwarty widok listy z mniejszymi odstępami. Bez tła i z mniejszą liczbą elementów kursu.',
  '<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister og oversikter. Vises ofte direkte under overskriften.':
    '<strong>Krótki opis:</strong> Krótki tekst wyświetlany na listach i w przeglądach. Często bezpośrednio pod nagłówkiem.',
  '<strong>Kort beskrivelse:</strong> En kort tekst som kan vises i lister.':
    '<strong>Krótki opis:</strong> Krótki tekst wyświetlany na listach.',
  '<strong>Kurs</strong> – Inneholder <code class="copytext">[kursliste]</code> for å vise alle kurs med filtre.':
    '<strong>Kursy</strong> – Zawiera <code class="copytext">[kursliste]</code> do wyświetlenia wszystkich kursów z filtrami.',
  '<strong>Kurskategorier</strong> – Inneholder <code class="copytext">[kurskategorier]</code> for å vise alle kategorier.':
    '<strong>Kategorie kursów</strong> – Zawiera <code class="copytext">[kurskategorier]</code> do wyświetlenia wszystkich kategorii.',
  '<strong>Kursliste (kun for kurskategorier):</strong> Tre alternativer styrer hvordan kategorien påvirker kurslisten:':
    '<strong>Lista kursów (tylko kategorie kursów):</strong> Trzy opcje określają, jak kategoria wpływa na listę kursów:',
  '<strong>Kursliste med filter </strong><span class="smal"><span class="copytext">[kursliste]</span></span><br><span class="copytext small">[kursliste kategori="web" sted="oslo" måned="9" språk="norsk" list_type="grid" filter="topp" knapper="signup_link" bilder="yes" vis="-sluttdato" st=sted/st=ikke-sted klasse="min-klasse"]</span>':
    '<strong>Lista kursów z filtrem </strong><span class="smal"><span class="copytext">[kursliste]</span></span><br><span class="copytext small">[kursliste kategori="web" sted="oslo" måned="9" språk="norsk" list_type="grid" filter="topp" knapper="signup_link" bilder="yes" vis="-sluttdato" st=sted/st=ikke-sted klasse="min-klasse"]</span>',
  '<strong>Kurssteder opprettes automatisk</strong> når du synkroniserer kurs fra Kursagenten. Du kan ikke legge til kurssteder manuelt her.':
    '<strong>Lokalizacje kursów tworzone są automatycznie</strong> podczas synchronizacji kursów z Kursagenten. Nie można ich tu dodać ręcznie.',
  '<strong>Kurssteder</strong> – Inneholder <code class="copytext">[kurssteder]</code> for å vise alle kurssteder.':
    '<strong>Lokalizacje kursów</strong> – Zawiera <code class="copytext">[kurssteder]</code> do wyświetlenia wszystkich lokalizacji kursów.',
  '<strong>Layout og struktur:</strong> Hvor elementene plasseres på siden (header, innhold, sidekolonne, footer).':
    '<strong>Układ i struktura:</strong> Gdzie elementy są umieszczone na stronie (nagłówek, treść, pasek boczny, stopka).',
  '<strong>Layout:</strong> Velg designet på siden. Dette styrer hvor/hvordan tittel, tekst, hovedbilde og kursliste vises.':
    '<strong>Układ:</strong> Wybierz projekt strony. Określa, gdzie/jak wyświetlane są tytuł, tekst, główny obraz i lista kursów.',
  '<strong>Layout</strong> bestemmer oppsettet av elementer på siden (header, kolonner, hooks).</br><strong>Listedesign</strong> bestemmer hvordan kursene vises i listen (standard, rutenett, ren, kompakt, dato og tittel). </br><strong>Visningstype</strong> bestemmer om du vil vise hovedkurs eller alle kursdatoer.':
    '<strong>Układ</strong> określa rozmieszczenie elementów na stronie (nagłówek, kolumny, hooki).</br><strong>Projekt listy</strong> określa sposób wyświetlania kursów na liście (standard, siatka, prosty, kompaktowy, data i tytuł). </br><strong>Typ wyświetlania</strong> określa, czy pokazywać kursy główne, czy wszystkie terminy kursów.',
  '<strong>Liste med instruktører </strong><span class="smal"><span class="copytext">[instruktorer]</span></span><br><span class="copytext small" style="color:#666">[instruktorer layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" skjul="Iris,Anna" utdrag="ja" beskrivelse="ja" klasse="min-klasse"]</span>':
    '<strong>Lista instruktorów </strong><span class="smal"><span class="copytext">[instruktorer]</span></span><br><span class="copytext small" style="color:#666">[instruktorer layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" skjul="Iris,Anna" utdrag="ja" beskrivelse="ja" klasse="min-klasse"]</span>',
  '<strong>Liste med kurskategorier </strong><span class="smal"><span class="copytext">[kurskategorier]</span></span><br><span class="copytext small" style="color:#666">[kurskategorier kilde="bilde/ikon" layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1  radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13" fontmaks="18" avstand="2em .5em" skygge="ja" vis="hovedkategorier/subkategorier/slug/standard" st=sted/st=ikke-sted utdrag="ja" klasse="min-klasse"]</span>':
    '<strong>Lista kategorii kursów </strong><span class="smal"><span class="copytext">[kurskategorier]</span></span><br><span class="copytext small" style="color:#666">[kurskategorier kilde="bilde/ikon" layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1  radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13" fontmaks="18" avstand="2em .5em" skygge="ja" vis="hovedkategorier/subkategorier/slug/standard" st=sted/st=ikke-sted utdrag="ja" klasse="min-klasse"]</span>',
  '<strong>Liste med kurssteder </strong><span class="smal"><span class="copytext">[kurssteder]</span></span><br><span class="copytext small" style="color:#666">[kurssteder layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" utdrag="ja" vis="standard/alta,oslo,bergen" region="østlandet" stedinfo="ja" klasse="min-klasse"]</span>':
    '<strong>Lista lokalizacji kursów </strong><span class="smal"><span class="copytext">[kurssteder]</span></span><br><span class="copytext small" style="color:#666">[kurssteder layout="stablet/rad/liste" grid=3 gridtablet=2 gridmobil=1 radavstand="1rem" stil="standard/kort" bildestr="100px" bildeform="avrundet/rund/firkantet/10px" bildeformat="4/3" overskrift="h3" fontmin="13px" fontmaks="15px" avstand="2em .5em" skygge="ja" utdrag="ja" vis="standard/alta,oslo,bergen" region="østlandet" stedinfo="ja" klasse="min-klasse"]</span>',
  '<strong>Listedesign:</strong> Velg designet på kurslisten (standard, rutenett, kompakt, enkle kort...)':
    '<strong>Projekt listy:</strong> Wybierz projekt listy kursów (standard, siatka, kompaktowy, proste karty...)',
  '<strong>Listedesign:</strong> Velg listedesign (standard, rutenett, kompakt, enkel liste, enkle kort).':
    '<strong>Projekt listy:</strong> Wybierz projekt listy (standard, siatka, kompaktowy, prosta lista, proste karty).',
  '<strong>Listedesign</strong> bestemmer hvordan kursene vises i listen (standard, rutenett, ren, kompakt, dato og tittel).':
    '<strong>Projekt listy</strong> określa sposób wyświetlania kursów na liście (standard, siatka, prosty, kompaktowy, data i tytuł).',
  '<strong>Manuell synkronisering:</strong> Gå til <a href="admin.php?page=kursinnstillinger">Synkronisering</a> og klikk på "Hent alle kurs fra Kursagenten". Dette bør gjøres første gang du setter opp pluginen, og ved behov for å oppdatere alle kursene samtidig.':
    '<strong>Synchronizacja ręczna:</strong> Przejdź do <a href="admin.php?page=kursinnstillinger">Synchronizacja</a> i kliknij „Pobierz wszystkie kursy z Kursagenten”. Zrób to przy pierwszej konfiguracji wtyczki oraz gdy trzeba zaktualizować wszystkie kursy naraz.',
  '<strong>Manuelt:</strong> Du kan endre region for individuelle steder ved å redigere kursstedet i <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Kurssteder</a>-oversikten.':
    '<strong>Ręcznie:</strong> Region poszczególnych lokalizacji można zmienić, edytując lokalizację kursu w przeglądzie <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Lokalizacje kursów</a>.',
  '<strong>Merk:</strong> Regioner må være aktivert for at filtreringen skal fungere. Du kan også endre region for individuelle kurssteder ved å redigere kursstedet i <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Kurssteder</a>-oversikten.':
    '<strong>Uwaga:</strong> Regiony muszą być włączone, aby filtrowanie działało. Region poszczególnych lokalizacji można też zmienić, edytując lokalizację w przeglądzie <a href="edit-tags.php?taxonomy=ka_course_location&post_type=ka_course">Lokalizacje kursów</a>.',
  '<strong>Navn:</strong> Instruktørens navn (kan deles opp i fornavn og etternavn).':
    '<strong>Nazwa:</strong> Imię i nazwisko instruktora (można podzielić na imię i nazwisko).',
  '<strong>Navn:</strong> Kategorinavnet (kommer fra Kursagenten, bør ikke endres).':
    '<strong>Nazwa:</strong> Nazwa kategorii (pochodzi z Kursagenten, nie należy jej zmieniać).',
  '<strong>Navn:</strong> Stedsnavnet (kommer fra Kursagenten, men kan endres via navnendring-funksjonen).':
    '<strong>Nazwa:</strong> Nazwa lokalizacji (pochodzi z Kursagenten, można ją zmienić przez funkcję zmiany nazwy).',
  '<strong>Navnendring på kurssteder:</strong><br>Du kan endre navn på kurssteder under <a href="%s">Synkronisering → Navnendring på kurssteder</a>. Når du endrer navn på et sted, blir også slugs (nettadressen) på kursene som har dette stedet oppdatert.<br> Det gamle stedet blir ikke slettet, men blir ikke lenger synlig på nettsiden.':
    '<strong>Zmiana nazwy lokalizacji kursów:</strong><br>Nazwy lokalizacji można zmienić w <a href="%s">Synchronizacja → Zmiana nazw lokalizacji kursów</a>. Po zmianie nazwy aktualizowane są też slugi (adresy URL) kursów z tą lokalizacją.<br> Stara lokalizacja nie jest usuwana, ale nie jest już widoczna na stronie.',
  '<strong>Når bør du bruke kortkoder?</strong> Hvis du bygger sidene med Elementor eller andre page builders, vil kortkoder sannsynligvis være det du må bruke.':
    '<strong>Kiedy używać shortcodów?</strong> Jeśli budujesz strony w Elementorze lub innym kreatorze, shortcody będą prawdopodobnie niezbędne.',
  '<strong>Obs:</strong> For at brukerne skal kunne slå filteret av og på, må du dra <em>«Ledige kurs»</em> inn i «Filtre i venstre kolonne» eller «Filtre over kurslisten» under Filterinnstillinger.':
    '<strong>Uwaga:</strong> Aby użytkownicy mogli włączać i wyłączać filtr, przeciągnij <em>„Dostępne kursy”</em> do „Filtry w lewej kolumnie” lub „Filtry nad listą kursów” w ustawieniach filtrów.',
  '<strong>OBS!</strong> Fyll inn <a href="#kursagenten-innstillinger" style="color:white; text-decoration: underline;">innstillinger fra Kursagenten</a> før du henter alle kursene dine (synkroniser alle kurs).':
    '<strong>UWAGA!</strong> Wprowadź <a href="#kursagenten-innstillinger" style="color:white; text-decoration: underline;">ustawienia z Kursagenten</a> przed pobraniem wszystkich kursów (synchronizacja wszystkich kursów).',
  '<strong>Overstyr profil fra Kursagenten:</strong> Aktiver denne for å kunne redigere navn, e-post og telefon. Når aktivert, vil ikke disse feltene oppdateres automatisk fra Kursagenten ved synkronisering.':
    '<strong>Nadpisz profil z Kursagenten:</strong> Włącz, aby edytować imię, e-mail i telefon. Po włączeniu pola nie będą automatycznie aktualizowane z Kursagenten podczas synchronizacji.',
  '<strong>Overstyr profilbilde:</strong> Aktiver denne for å bruke et eget opplastet bilde i stedet for bildet fra Kursagenten.':
    '<strong>Nadpisz obraz profilowy:</strong> Włącz, aby użyć własnego przesłanego obrazu zamiast obrazu z Kursagenten.',
  '<strong>Page builder (Kadence Elements, Elementor m.fl.):</strong> Hvis du vil styre single/archive/taksonomi med page builder-betingelser, gå til <a href="admin.php?page=design#section-enkeltkurs">Kursdesign → Enkeltkurs</a> og aktiver <em>Bruk WordPress sitt standard malhierarki</em>. Da lar pluginen tema/page builder velge mal uten å tvinge pluginens egne layout-filer.':
    '<strong>Kreator stron (Kadence Elements, Elementor itd.):</strong> Aby sterować single/archive/taksonomią warunkami kreatora, przejdź do <a href="admin.php?page=design#section-enkeltkurs">Projekt kursu → Pojedynczy kurs</a> i włącz <em>Użyj domyślnej hierarchii szablonów WordPress</em>. Wtyczka pozwoli motywowi/kreatorowi wybrać szablon bez wymuszania własnych plików układu.',
  '<strong>Permalenker:</strong> Ved endring av URL-innstillinger, lagre «Permalenker» på nytt.':
    '<strong>Bezpośrednie odnośniki:</strong> Po zmianie ustawień URL zapisz ponownie „Bezpośrednie odnośniki”.',
  '<strong>Plassering:</strong> Filtrene velges ved å dra dem til korrekt plassering. Du kan velge å vise dem til venstre for kurslisten og/eller over kurslisten.':
    '<strong>Umiejscowienie:</strong> Filtry wybiera się, przeciągając je we właściwe miejsce. Można je pokazać po lewej od listy kursów i/lub nad listą kursów.',
  '<strong>Profil - rundt bilde og tittel:</strong> Rundt bilde, deretter tittel og beskrivelse, deretter kursliste':
    '<strong>Profil — wokół obrazu i tytułu:</strong> Wokół obrazu, następnie tytuł i opis, potem lista kursów',
  '<strong>Profilbilde:</strong> Et hovedbilde som vises på instruktørsiden og i lister. Dette kan være fra Kursagenten eller et eget opplastet bilde.':
    '<strong>Obraz profilowy:</strong> Główny obraz na stronie instruktora i na listach. Może pochodzić z Kursagenten lub być własnym przesłanym obrazem.',
  '<strong>Region:</strong> (Hvis regioner er aktivert) Velg hvilken region stedet tilhører.':
    '<strong>Region:</strong> (Gdy regiony są włączone) Wybierz region, do którego należy lokalizacja.',
  '<strong>Regioner:</strong><br>Du kan aktivere og administrere regioner under <a href="%s">Synkronisering → Regioner</a>.':
    '<strong>Regiony:</strong><br>Regiony można włączyć i zarządzać nimi w <a href="%s">Synchronizacja → Regiony</a>.',
  '<strong>Regioner:</strong><br>Regioner er aktivert. Du kan administrere regioninndelingen under <a href="%s">Synkronisering → Regioner</a>. Tilhørighet til en region kan endres under hvert kurssted.':
    '<strong>Regiony:</strong><br>Regiony są włączone. Podział regionalny można zarządzać w <a href="%s">Synchronizacja → Regiony</a>. Przypisanie do regionu można zmienić przy każdej lokalizacji kursu.',
  '<strong>Rekkefølge på elementer:</strong> Hvilken rekkefølge informasjonen vises i (introtekst, hovedinnhold, kursdatoer, instruktører, osv.).':
    '<strong>Kolejność elementów:</strong> Kolejność wyświetlania informacji (tekst wstępu, główna treść, terminy kursów, instruktorzy itd.).',
  '<strong>Rekkefølge:</strong> Du kan også dra i filtrene for å endre rekkefølgen de vises i.':
    '<strong>Kolejność:</strong> Filtry można też przeciągać, aby zmienić kolejność wyświetlania.',
  '<strong>Ren og enkel liste:</strong> Basert på Standard liste, men uten bakgrunn, og med færre kurselementer':
    '<strong>Prosta i czysta lista:</strong> Na bazie listy standardowej, ale bez tła i z mniejszą liczbą elementów kursu',
  '<strong>Separate designmaler:</strong> Aktiver dette for å kunne velge forskjellige designmaler for kategorier, steder og instruktører.':
    '<strong>Osobne szablony projektu:</strong> Włącz, aby wybierać różne szablony dla kategorii, lokalizacji i instruktorów.',
  '<strong>Skjul i automenyer:</strong> Når aktivert, skjules kategorien i autogenererte menyer som bruker kortkoden <code>[ka-meny]</code>. Dette er nyttig hvis du har kategorier som ikke skal vises i hovedmenyen.':
    '<strong>Ukryj w automatycznych menu:</strong> Po włączeniu kategoria jest ukryta w automatycznie generowanych menu ze shortcode <code>[ka-meny]</code>. Przydatne dla kategorii niewidocznych w menu głównym.',
  '<strong>Skjul i oversiktslister:</strong> Når aktivert, skjules kategorien i kortkoder som <code>[kurskategorier]</code> og lignende lister. Kategorien vil fortsatt være tilgjengelig direkte via URL.':
    '<strong>Ukryj na listach przeglądowych:</strong> Po włączeniu kategoria jest ukryta w shortcodach takich jak <code>[kurskategorier]</code> i podobnych listach. Nadal dostępna bezpośrednio przez URL.',
  '<strong>Slug:</strong> URL-vennlig versjon av navnet (bør ikke endres da det kan føre til ødelagte lenker).':
    '<strong>Slug:</strong> Przyjazna dla URL wersja nazwy (nie należy zmieniać — może to zepsuć linki).',
  '<strong>Slug:</strong> URL-vennlig versjon av navnet (oppdateres automatisk ved navnendring).':
    '<strong>Slug:</strong> Przyjazna dla URL wersja nazwy (aktualizowana automatycznie przy zmianie nazwy).',
  '<strong>Standard - med bilde og beskrivelse:</strong> Tittel og beskrivelse, deretter bilde og beskrivelse, deretter kursliste':
    '<strong>Standard — z obrazem i opisem:</strong> Tytuł i opis, następnie obraz i opis, potem lista kursów',
  '<strong>Standard:</strong> 1 kolonne': '<strong>Standard:</strong> 1 kolumna',
  '<strong>Standard:</strong> 100px': '<strong>Standard:</strong> 100px',
  '<strong>Standard:</strong> 1rem': '<strong>Standard:</strong> 1rem',
  '<strong>Standard:</strong> 2 kolonner': '<strong>Standard:</strong> 2 kolumny',
  '<strong>Standard:</strong> 2em .5em': '<strong>Standard:</strong> 2em .5em',
  '<strong>Standard:</strong> 3 kolonner': '<strong>Standard:</strong> 3 kolumny',
  '<strong>Standard:</strong> En tradisjonell listevisning med kurskort, ett kurs per rad/boks.':
    '<strong>Standard:</strong> Tradycyjny widok listy z kartami kursów — jeden kurs na wiersz/boks.',
  '<strong>Synkronisering:</strong> Hvis kurs ikke oppdateres automatisk, sjekk at webhooks er konfigurert korrekt i Kursagenten.':
    '<strong>Synchronizacja:</strong> Jeśli kursy nie aktualizują się automatycznie, sprawdź konfigurację webhooków w Kursagenten.',
  '<strong>Tagger eller lister:</strong> Filtrene kan velges som tagger eller avkrysningsliste. Tagger er knapper som velger ett filter av gangen, og avkrysningsliste er en liste som kan velge flere filter samtidig.':
    '<strong>Tagi lub listy:</strong> Filtry można pokazać jako tagi lub listę checkboxów. Tagi to przyciski wybierające jeden filtr; checkboxy pozwalają wybrać wiele filtrów naraz.',
  '<strong>Telefon:</strong> Instruktørens telefonnummer.':
    '<strong>Telefon:</strong> Numer telefonu instruktora.',
  '<strong>Test:</strong> Besøk frontend og se at det ser ut som det skal.':
    '<strong>Test:</strong> Odwiedź frontend i sprawdź, czy wygląda zgodnie z oczekiwaniami.',
  '<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på instruktørsiden.':
    '<strong>Rozszerzony opis:</strong> Dłuższy tekst z formatowaniem HTML na stronie instruktora.',
  '<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på kategorisiden.':
    '<strong>Rozszerzony opis:</strong> Dłuższy tekst z formatowaniem HTML na stronie kategorii.',
  '<strong>Utvidet beskrivelse:</strong> En lengre tekst med HTML-formatering som vises på stedssiden.':
    '<strong>Rozszerzony opis:</strong> Dłuższy tekst z formatowaniem HTML na stronie lokalizacji.',
  '<strong>Utvidet beskrivelse:</strong> En lengre tekst med rik tekstformatering (HTML) som vises på taksonomisiden. Her kan du beskrive kategorien, stedet eller instruktøren med tekst, bilder og annen informasjon.':
    '<strong>Rozszerzony opis:</strong> Dłuższy tekst z formatowaniem rich text (HTML) na stronie taksonomii. Opisz kategorię, lokalizację lub instruktora tekstem, obrazami i innymi informacjami.',
  '<strong>Velge eksisterende side:</strong> Du kan også velge en eksisterende WordPress-side fra dropdown-menyen og tilordne den til en funksjon. Lim inn kortkoden manuelt. Koden kan du kopiere fra ikonet <i class="ka-icon icon-code-simple-solid-full" style="height: 14px;"></i> ved siden av navnet.':
    '<strong>Wybór istniejącej strony:</strong> Możesz wybrać istniejącą stronę WordPress z listy rozwijanej i przypisać ją do funkcji. Wklej shortcode ręcznie. Kod skopiujesz z ikony <i class="ka-icon icon-code-simple-solid-full" style="height: 14px;"></i> obok nazwy.',
  '<strong>Viktig:</strong> Endringer i designmal kan kreve oppdatering/refresh av cache og permalenker, spesielt ved utstrakte URL-tilpasninger.':
    '<strong>Ważne:</strong> Zmiany szablonu projektu mogą wymagać odświeżenia cache i bezpośrednich odnośników, zwłaszcza przy rozbudowanych dostosowaniach URL.',
  '<strong>Viktig:</strong> Navnet på kursstedet i WordPress-administrasjonen er skrivebeskyttet når det kommer fra Kursagenten. For å endre navnet som vises på nettsiden, bruk navnendring-funksjonen i stedet for å redigere taksonomien direkte.':
    '<strong>Ważne:</strong> Nazwa lokalizacji kursu w panelu WordPress jest tylko do odczytu, gdy pochodzi z Kursagenten. Aby zmienić nazwę na stronie, użyj funkcji zmiany nazwy zamiast edycji taksonomii.',
  '<strong>Viktig:</strong> Når du overskriver felter, vil de ikke lenger oppdateres automatisk fra Kursagenten. Du må manuelt oppdatere dem hvis det er endringer i Kursagenten.':
    '<strong>Ważne:</strong> Po nadpisaniu pól nie będą one automatycznie aktualizowane z Kursagenten. Musisz je zaktualizować ręcznie po zmianach w Kursagenten.',
  '<strong>Vis bilder:</strong> Skru av/på bilder i listen. Best egnet om du har lastet opp bilder til kursene i Kursagenten.':
    '<strong>Pokaż obrazy:</strong> Włącz/wyłącz obrazy na liście. Najlepiej, gdy masz przesłane obrazy kursów w Kursagenten.',
  '<strong>Vis:</strong> Kategorien vises i filteret, og kurs tagget med kategorien vises i kurslisten.<br><strong>Skjul kun kategorien fra filter:</strong> Kategorien skjules fra filteret, men kurs tagget med denne kategorien vises fortsatt i kurslisten (under sine øvrige kategorier).<br><strong>Skjul kategorien og alle tilhørende kurs:</strong> Verken kategorien eller kurs tagget med denne vises i kurslisten – nyttig for interne kategorier.':
    '<strong>Pokaż:</strong> Kategoria jest w filtrze, a kursy z tą kategorią na liście kursów.<br><strong>Ukryj tylko kategorię w filtrze:</strong> Kategoria ukryta w filtrze, ale kursy z nią nadal na liście (pod innymi kategoriami).<br><strong>Ukryj kategorię i powiązane kursy:</strong> Ani kategoria, ani kursy z nią nie pojawiają się na liście — przydatne dla kategorii wewnętrznych.',
  '<strong>Visning av liste:</strong> I venstre kolonne vil listene vises som avkrysningsliste, mens over kursliten vises lister som dropdown-menyer.':
    '<strong>Wyświetlanie list:</strong> W lewej kolumnie listy jako checkboxy; nad listą kursów jako menu rozwijane.',
  '<strong>Visningstype:</strong> Velg om du vil vise hovedkurs (ett kurs per rad/boks) eller alle kursdatoer (tilsvarende "Kursliste med filter").':
    '<strong>Typ wyświetlania:</strong> Wybierz kursy główne (jeden kurs na wiersz/boks) lub wszystkie terminy (jak „Lista kursów z filtrem”).',
  '<strong>Visningstype</strong> bestemmer om du vil vise hovedkurs eller alle kursdatoer.':
    '<strong>Typ wyświetlania</strong> określa, czy pokazywać kursy główne, czy wszystkie terminy kursów.',
  '<strong>Visuelle detaljer:</strong> Styling, spacing og annet visuelt design.':
    '<strong>Szczegóły wizualne:</strong> Styl, odstępy i inne elementy wizualne.',
  '✓ (kan overstyres i AIOSEO Pro)': '✓ (można nadpisać w AIOSEO Pro)',
  '✓ (kan overstyres i SEOPress Pro)': '✓ (można nadpisać w SEOPress Pro)',
};

// Merge part 2 from external file if present
try {
  Object.assign(PL, require('./pl_PL-patch-trans-data-part2.js'));
} catch (_) {
  /* part 2 added separately */
}

const missing = ref.filter(({ k }) => PL[k] === undefined);
if (missing.length) {
  console.error('Still missing', missing.length, 'entries — add to pl_PL-patch-trans-data-part2.js');
  fs.writeFileSync(
    path.join(__dirname, '_pl-missing-keys.json'),
    JSON.stringify(missing.map(({ k, en }) => ({ k, en })), null, 2)
  );
  process.exit(1);
}

const lines = ['/** @type {Record<string, string>} */', 'module.exports = {'];
for (const { k } of ref) {
  lines.push(`${JSON.stringify(k)}: ${JSON.stringify(PL[k])},`);
}
lines.push('};', '');
fs.writeFileSync(path.join(__dirname, 'pl_PL-patch-trans-data.js'), lines.join('\n'), 'utf8');
console.log('Wrote pl_PL-patch-trans-data.js', ref.length, 'entries');
