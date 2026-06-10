/**
 * One-off helper: merge frontend EN translations into translations-en_US.json
 */
const fs = require('fs');
const path = require('path');

const seedPath = path.join(__dirname, '..', 'lang', 'translations-en_US.json');
const seed = JSON.parse(fs.readFileSync(seedPath, 'utf8'));
const missing = JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'lang', '_missing-keys.json'), 'utf8'));

const en = {
  ' - side %d av %d': ' - page %d of %d',
  '– side %1$d av %2$d': '– page %1$d of %2$d',
  '%1$d %2$s valgt': '%1$d %2$s selected',
  '%d %s valgt': '%d %s selected',
  '%d kurs': '%d courses',
  '+%d flere': '+%d more',
  'Alle (%d)': 'All (%d)',
  'Alle tilgjengelige kurssteder og datoer': 'All available course locations and dates',
  'Begrenser til termer med kurs på angitt sted. Start med "ikke-" for å ekskludere. La stå tom for alle.':
    'Limits to terms with courses at the specified location. Start with "not-" to exclude. Leave empty for all.',
  'Bilde av %s': 'Image of %s',
  'Bilde av kurs i %s': 'Image of course in %s',
  'Bilde for kurs i %s': 'Image for course in %s',
  Bruk: 'Apply',
  'Del dette kurset': 'Share this course',
  'Det er for øyeblikket ingen instruktører å vise.': 'There are currently no instructors to display.',
  'Det er for øyeblikket ingen kurssteder å vise.': 'There are currently no course locations to display.',
  'Det er for øyeblikket ingen relaterte kurs å vise.': 'There are currently no related courses to display.',
  'Det er ikke satt opp dato for nye kurs. Meld din interesse for å få mer informasjon eller å sette deg på venteliste.':
    'No date has been set for new courses. Register your interest for more information or to join the waiting list.',
  'Du kan melde deg på kurset nå. Etter påmelding vil du få mer informasjon.':
    'You can register for the course now. After registration you will receive more information.',
  'En feil oppstod under filtreringen.': 'An error occurred while filtering.',
  'En feil oppstod under henting av filter counts': 'An error occurred while fetching filter counts',
  'En ukjent feil oppstod under filtreringen.': 'An unknown error occurred while filtering.',
  'Etter påmelding vil du få en e-post med mer informasjon om kurset, og hvordan det skal gjennomføres.':
    'After registration you will receive an email with more information about the course and how it will be conducted.',
  'Etter påmelding vil du få en e-post med mer informasjon om kurset.':
    'After registration you will receive an email with more information about the course.',
  'f.eks. oslo eller ikke-oslo': 'e.g. oslo or not-oslo',
  'Filtrer kurs': 'Filter courses',
  'Finn det perfekte kurset for deg og din bedrift': 'Find the perfect course for you and your business',
  'Finner du ikke det du leter etter?': "Can't find what you're looking for?",
  'Fjern filter': 'Remove filter',
  'Flere instruktører': 'More instructors',
  'Flere kurskategorier': 'More course categories',
  'Flere kurssteder': 'More course locations',
  'For kurstilbydere med få kurs og 1/ingen kategorier: viser kun en flat liste med hovedkurs uten kategorinivå.':
    'For course providers with few courses and 1/no categories: shows only a flat list of main courses without category levels.',
  'Forhåndsvisning: fant ingen publiserte enkeltkurs å bruke som eksempel.':
    'Preview: no published single courses found to use as an example.',
  'Forhåndsvisning: ingen kommende kurs funnet.': 'Preview: no upcoming courses found.',
  'Forhåndsvisning: ingen kontaktinformasjon registrert for valgt kurs.':
    'Preview: no contact information registered for the selected course.',
  'Forhåndsvisning: ingen kursoversiktsside er konfigurert ennå.':
    'Preview: no course overview page has been configured yet.',
  'Forhåndsvisning: ingen kurstider registrert for valgt kurs.':
    'Preview: no course times registered for the selected course.',
  'Forhåndsvisning: ingen påmeldingslenke for valgt kurs.':
    'Preview: no registration link for the selected course.',
  'Forhåndsvisning: kurset har ingen beskrivelse fra Kursagenten.':
    'Preview: the course has no description from Kursagenten.',
  Forrige: 'Previous',
  'Fra A til Å': 'From A to Z',
  'Fra Å til A': 'From Z to A',
  Fullt: 'Full',
  'Ingen alternativer tilgjengelig': 'No options available',
  'Ingen elementer funnet.': 'No items found.',
  'Ingen filtre er konfigurert.': 'No filters are configured.',
  'Ingen kurs funnet. Fjern ett eller flere filtre, eller nullstill alle filtre.':
    'No courses found. Remove one or more filters, or reset all filters.',
  'Ingen kurs tilgjengelige for dette filteret': 'No courses available for this filter',
  'Ingen kurs tilgjengelige for øyeblikket.': 'No courses available at the moment.',
  'Ingen kurs tilgjengelige med valgte filtre. Nullstill filtre hvis du står fast.':
    'No courses available with the selected filters. Reset filters if you are stuck.',
  'Ingen kurs tilgjengelige.': 'No courses available.',
  'Ingen kursdatoer tilgjengelig for øyeblikket.': 'No course dates available at the moment.',
  'Ingen treff med valgte filtre. Prøv et annet søk eller juster filtrene.':
    'No matches with the selected filters. Try a different search or adjust the filters.',
  'Instruktør:': 'Instructor:',
  'Instruktører:': 'Instructors:',
  'Klar til å melde deg på?': 'Ready to register?',
  Kontakt: 'Contact',
  'Kontakt oss': 'Contact us',
  Kontaktinformasjon: 'Contact information',
  'Kort beskrivelse:': 'Short description:',
  'Kun for positiv filter (f.eks. nettbasert): skjuler stedsfilteret på kurslistesiden. Ved "ikke-"-filter vises filteret slik at bruker kan velge annet sted.':
    'Only for positive filter (e.g. online): hides the location filter on the course list page. With a "not-" filter, the filter is shown so the user can choose another location.',
  'Kunne ikke laste filtrene. Vennligst prøv igjen.': 'Could not load filters. Please try again.',
  'Kurs i samme kategori': 'Courses in the same category',
  'Kursagenten automenyer': 'Kursagenten auto menus',
  'Kursdager:': 'Course days:',
  'Kurset er fullt': 'The course is full',
  'Kurset er fullt. Du kan melde din interesse for å få mer informasjon eller å sette deg på venteliste.':
    'The course is full. You can register your interest for more information or to join the waiting list.',
  'Kurset varer fra %1$s til %2$s': 'The course runs from %1$s to %2$s',
  'Kurset varer fra %s': 'The course runs from %s',
  Kursinformasjon: 'Course information',
  'Kurslokale:': 'Course room:',
  Kursoversikt: 'Course overview',
  Kurspåmelding: 'Course registration',
  'Kurstider og steder': 'Course times and locations',
  'Kurstider:': 'Course times:',
  'Ledige plasser': 'Available places',
  'Legg til ekstra Wordpress innhold': 'Add extra WordPress content',
  'Les mer': 'Read more',
  'Lokasjon:': 'Location:',
  'Lukk filter': 'Close filter',
  'Meld deg på': 'Register',
  'Mer info': 'More info',
  'Mer informasjon': 'More information',
  Moderne: 'Modern',
  Neste: 'Next',
  'Neste 3 måneder': 'Next 3 months',
  'Neste 6 måneder': 'Next 6 months',
  'Neste kurs (fullt)': 'Next course (full)',
  'Neste kurs:': 'Next course:',
  'Neste uke': 'Next week',
  'Neste år': 'Next year',
  'Nullstill alle filtre': 'Reset all filters',
  'Nullstill dato': 'Reset date',
  'Nullstill filter': 'Reset filters',
  'Om kurset': 'About the course',
  oversikten: 'the overview',
  'Pris høy til lav': 'Price high to low',
  'Pris lav til høy': 'Price low to high',
  'Pris:': 'Price:',
  'Prøv igjen': 'Try again',
  'Prøv å endre dine filtervalg eller nullstill alle filtre for å se alle tilgjengelige kurs.':
    'Try changing your filter choices or reset all filters to see all available courses.',
  'På forespørsel': 'On request',
  Påmelding: 'Registration',
  'Påmeldingsfrist:': 'Registration deadline:',
  'Rediger Wordpress innhold': 'Edit WordPress content',
  'Relaterte kurs': 'Related courses',
  'Resten av året': 'Rest of the year',
  'Rom:': 'Room:',
  'Se detaljer': 'View details',
  'Se kurs: %s': 'View course: %s',
  'Se kursdetaljer': 'View course details',
  'Se neste dato': 'See next date',
  'Seneste dato': 'Latest date',
  'Sikkerhetssjekk feilet': 'Security check failed',
  'Sikkerhetssjekk feilet. Vennligst oppdater siden og prøv igjen.':
    'Security check failed. Please refresh the page and try again.',
  'Sikre deg plass på dette kurset nå. Har du spørsmål, ikke nøl med å kontakte oss.':
    'Secure your place on this course now. If you have questions, do not hesitate to contact us.',
  'Skjul stedsfilter i videre visning': 'Hide location filter in further views',
  'Sluttdato:': 'End date:',
  'Slutter:': 'Ends:',
  'Sorter etter': 'Sort by',
  'Spesifikke lokasjoner': 'Specific locations',
  'Språk:': 'Language:',
  'Startdato:': 'Start date:',
  'Starter:': 'Starts:',
  'Sted:': 'Location:',
  'Søk etter kurs...': 'Search for courses...',
  'Ta kontakt med oss, så hjelper vi deg med å finne det perfekte kurset for dine behov.':
    'Contact us and we will help you find the perfect course for your needs.',
  'Tidligste dato': 'Earliest date',
  'Tidspunkt:': 'Time:',
  'Tilbake til %s': 'Back to %s',
  'Underkategorier:': 'Subcategories:',
  'Utvid %s': 'Expand %s',
  'Varighet:': 'Duration:',
  Velg: 'Select',
  'Velg datoer': 'Select dates',
  'Velg fra-til dato': 'Select from-to date',
  'Velg periode': 'Select period',
  'Vis alle kurs': 'Show all courses',
  'Vis antall kurs': 'Show number of courses',
  'Vis flere lokasjoner': 'Show more locations',
  'Vis færre': 'Show fewer',
  'Vis hovedkategorier': 'Show main categories',
  'Vis i Google Maps': 'View in Google Maps',
  'Vis kun kategorier med kurs på angitt sted (valgfritt)':
    'Show only categories with courses at the specified location (optional)',
  'Vis kun kurs, ikke kategorier': 'Show only courses, not categories',
  'Vis kun ledige plasser': 'Show only available places',
  'Vis kurs': 'View course',
  'Vis kurs i %s': 'View courses in %s',
  'Vis menypunkter:': 'Show menu items:',
  'Vis mer': 'Show more',
  'Vis resultater': 'Show results',
  'Vis subkategorier': 'Show subcategories',
  'Åpne %s i Google Maps': 'Open %s in Google Maps',
  'Åpne adresse i Google Maps': 'Open address in Google Maps',
  'Åpne i Google Maps': 'Open in Google Maps',
};

let added = 0;
const noEn = [];
for (const key of missing) {
  if (!seed[key] && en[key]) {
    seed[key] = en[key];
    added++;
  } else if (!seed[key]) {
    noEn.push(key);
  }
}

fs.writeFileSync(seedPath, JSON.stringify(seed, null, 2) + '\n', 'utf8');
console.log(`Added ${added} translations`);
if (noEn.length) {
  console.log('Missing EN mapping for:', noEn);
}
