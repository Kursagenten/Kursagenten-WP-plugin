/**
 * Build complete fr_FR seed files with manual French translations.
 * Usage: node scripts/build-fr_FR-all.js
 */
const fs = require('fs');
const path = require('path');

const LANG = path.join(__dirname, '..', 'lang');
const enT = JSON.parse(fs.readFileSync(path.join(LANG, 'translations-en_US.json'), 'utf8'));
const enB = JSON.parse(fs.readFileSync(path.join(LANG, 'block-editor-en_US.json'), 'utf8'));

const BRANDS = [
  'Kursagenten', 'WordPress', 'Wordpress', 'YouTube', 'Facebook', 'Instagram',
  'LinkedIn', 'Google Maps', 'Trello', 'Elementor', 'Kadence', 'HMS', 'SEO', 'srcset',
  'Gravatar', 'Gutenberg', 'Cloudflare', 'Rank Math', 'AIOSEO', 'SEOPress', 'Slim SEO',
  'jQuery', 'Apache', 'XML-RPC', 'Open Graph', 'Twitter Cards', 'CourseCreated',
  'CourseUpdated', 'Provider ID', 'Provider GUID', 'Kursadmin', 'ka-meny', 'ka_course',
  'ka_coursedate', 'ka_coursecategory', 'ka_course_location', 'ka_instructors',
];

function protectBrands(text) {
  const tokens = [];
  let out = text;
  BRANDS.forEach((brand, i) => {
    const re = new RegExp(brand.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
    out = out.replace(re, () => {
      const t = `__BR${i}__`;
      tokens.push({ t, brand });
      return t;
    });
  });
  return { out, tokens };
}

function restoreBrands(text, tokens) {
  let out = text;
  tokens.forEach(({ t, brand }) => {
    out = out.split(t).join(brand);
  });
  return out;
}

/** Rule-based EN→FR for plugin strings (manual quality translations). */
function translateEn(en) {
  const { out: protectedEn, tokens } = protectBrands(en);

  const G = {
    'Save': 'Enregistrer',
    'Cancel': 'Annuler',
    'Delete': 'Supprimer',
    'Edit': 'Modifier',
    'Close': 'Fermer',
    'Search': 'Rechercher',
    'Filter': 'Filtrer',
    'Show': 'Afficher',
    'Hide': 'Masquer',
    'Yes': 'Oui',
    'No': 'Non',
    'All': 'Tous',
    'None': 'Aucun',
    'Default': 'Par défaut',
    'Standard': 'Standard',
    'Custom': 'Personnalisé',
    'Apply': 'Appliquer',
    'Remove': 'Retirer',
    'Clear': 'Effacer',
    'Upload': 'Téléverser',
    'Publish': 'Publier',
    'Draft': 'Brouillon',
    'Published': 'Publié',
    'Settings': 'Paramètres',
    'Design': 'Design',
    'Layout': 'Mise en page',
    'Image': 'Image',
    'Images': 'Images',
    'Title': 'Titre',
    'Description': 'Description',
    'Email': 'E-mail',
    'Phone': 'Téléphone',
    'Address': 'Adresse',
    'Name': 'Nom',
    'Date': 'Date',
    'Time': 'Heure',
    'Price': 'Prix',
    'Course': 'Cours',
    'Courses': 'Cours',
    'Region': 'Région',
    'Regions': 'Régions',
    'Location': 'Lieu',
    'Instructor': 'Formateur',
    'Instructors': 'Formateurs',
    'Category': 'Catégorie',
    'Categories': 'Catégories',
    'Grid': 'Grille',
    'List': 'Liste',
    'Desktop': 'Bureau',
    'Mobile': 'Mobile',
    'Tablet': 'Tablette',
    'Background': 'Arrière-plan',
    'Color': 'Couleur',
    'Colors': 'Couleurs',
    'Spacing': 'Espacement',
    'Shadow': 'Ombre',
    'Border': 'Bordure',
    'Text': 'Texte',
    'Bold': 'Gras',
    'Normal': 'Normal',
    'Small': 'Petit',
    'Large': 'Grand',
    'Medium': 'Moyen',
    'Top': 'Haut',
    'Bottom': 'Bas',
    'Left': 'Gauche',
    'Right': 'Droite',
    'Centered': 'Centré',
    'Rounded': 'Arrondi',
    'Square': 'Carré',
    'Solid': 'Plein',
    'Dashed': 'Tirets',
    'Dotted': 'Pointillé',
    'General': 'Général',
    'Overview': 'Aperçu',
    'Documentation': 'Documentation',
    'Example': 'Exemple',
    'Optional': 'Facultatif',
    'Required': 'Obligatoire',
    'Status': 'Statut',
    'Action': 'Action',
    'Actions': 'Actions',
    'Previous': 'Précédent',
    'Next': 'Suivant',
    'Contact': 'Contact',
    'Payment': 'Paiement',
    'Link': 'Lien',
    'Links': 'Liens',
    'Menu': 'Menu',
    'Menus': 'Menus',
    'Page': 'Page',
    'Pages': 'Pages',
    'Footer': 'Pied de page',
    'Header': 'En-tête',
    'Content': 'Contenu',
    'Information': 'Informations',
    'Warning': 'Avertissement',
    'Important': 'Important',
    'Note': 'Remarque',
    'Loading': 'Chargement',
    'Error': 'Erreur',
    'Success': 'Succès',
    'Select': 'Sélectionner',
    'Choose': 'Choisir',
    'Enable': 'Activer',
    'Disable': 'Désactiver',
    'Active': 'Actif',
    'Inactive': 'Inactif',
    'Visible': 'Visible',
    'Hidden': 'Masqué',
    'Automatic': 'Automatique',
    'Manual': 'Manuel',
    'Synchronization': 'Synchronisation',
    'Webhooks': 'Webhooks',
    'License key': 'Clé de licence',
    'Company name': 'Nom de l\'entreprise',
    'Company information': 'Informations sur l\'entreprise',
    'First name': 'Prénom',
    'Last name': 'Nom de famille',
    'Full name': 'Nom complet',
    'Thumbnail': 'Miniature',
    'Placeholder image': 'Image de remplacement',
    'Featured image': 'Image à la une',
    'Main image': 'Image principale',
    'Profile image': 'Photo de profil',
    'Alternative image': 'Image alternative',
    'Course design': 'Design des cours',
    'Course list': 'Liste des cours',
    'Course categories': 'Catégories de cours',
    'Course locations': 'Lieux de cours',
    'Course dates': 'Dates de cours',
    'Course settings': 'Paramètres des cours',
    'Single course': 'Cours individuel',
    'Shortcodes': 'Shortcodes',
    'Taxonomy pages': 'Pages de taxonomie',
    'Filter settings': 'Paramètres de filtre',
    'Advanced settings': 'Paramètres avancés',
    'Get started': 'Commencer',
    'Read more': 'En savoir plus',
    'View details': 'Voir les détails',
    'Register': 'S\'inscrire',
    'Register now': 'S\'inscrire maintenant',
    'Register interest': 'Manifester son intérêt',
    'Try again': 'Réessayer',
    'Reset to default': 'Réinitialiser par défaut',
    'Check for updates': 'Vérifier les mises à jour',
    'All locations': 'Tous les lieux',
    'All courses': 'Tous les cours',
    'All instructors': 'Tous les formateurs',
    'All course categories': 'Toutes les catégories de cours',
    'All course locations': 'Tous les lieux de cours',
    'No region': 'Aucune région',
    'No instructors found.': 'Aucun formateur trouvé.',
    'No course locations found.': 'Aucun lieu de cours trouvé.',
    'No parent categories found.': 'Aucune catégorie parente trouvée.',
    'No options available.': 'Aucune option disponible.',
    '0 = no limit': '0 = sans limite',
    'Automatic (recommended)': 'Automatique (recommandé)',
    'Automatic uses responsive srcset when possible for sharper images.': 'Le mode automatique utilise srcset responsive lorsque possible pour des images plus nettes.',
    'Background hover': 'Survol de l\'arrière-plan',
    'Background color': 'Couleur d\'arrière-plan',
    'Background color behind image': 'Couleur d\'arrière-plan derrière l\'image',
    'Background image': 'Image d\'arrière-plan',
    'Background source': 'Source de l\'arrière-plan',
    'Image format': 'Format d\'image',
    'Image size': 'Taille de l\'image',
    'Image quality': 'Qualité de l\'image',
    'Image shapes': 'Formes d\'image',
    'Use card design': 'Utiliser le design en carte',
    'Data source': 'Source de données',
    'Element alignment': 'Alignement des éléments',
    'Element card': 'Carte d\'élément',
    'Font size for description': 'Taille de police pour la description',
    'Font size for title': 'Taille de police pour le titre',
    'Font weight text': 'Graisse de police du texte',
    'Font weight title': 'Graisse de police du titre',
    'Parent category (optional)': 'Catégorie parente (facultatif)',
    'Full size': 'Taille complète',
    'Main categories': 'Catégories principales',
    'Hover': 'Survol',
    'RIGHT': 'DROITE',
    'LEFT': 'GAUCHE',
    'TOP': 'HAUT',
    'BOTTOM': 'BAS',
    'Extra bold': 'Extra gras',
    'Extra strong': 'Extra épais',
    'Extra large': 'Très grand',
    'Semi-bold': 'Demi-gras',
    'Strong': 'Épais',
    'Thin': 'Fin',
    'Regular': 'Normal',
    'Subtle': 'Discret',
    'More rounded': 'Plus arrondi',
    'more information': 'plus d\'informations',
    'Dark overlay (%)': 'Superposition sombre (%)',
    'Border style': 'Style de bordure',
    'Border radius': 'Rayon de bordure',
    'Source type': 'Type de source',
    'Columns': 'Colonnes',
    'Card margin': 'Marge de la carte',
    'Card padding': 'Marge intérieure de la carte',
    'Kursagenten Taxonomy': 'Taxonomie Kursagenten',
    'Layout and style': 'Mise en page et style',
    'Simple list': 'Liste simple',
    'Max words in description': 'Nombre max. de mots dans la description',
    'Max words in long description': 'Nombre max. de mots dans la description longue',
    'Row spacing': 'Espacement des lignes',
    'Preset styles': 'Styles prédéfinis',
    'Row card': 'Carte en ligne',
    'Row standard': 'Ligne standard',
    'Row extended description': 'Ligne description étendue',
    'Edit in Element card': 'Modifier dans la carte d\'élément',
    'Stacked card': 'Carte empilée',
    'Stacked inset card': 'Carte empilée en retrait',
    'Stacked overlapping card': 'Carte empilée chevauchante',
    'Stacked standard': 'Empilé standard',
    'Standard profile image': 'Photo de profil standard',
    'Start directly with visual style choices': 'Commencer directement par les choix de style visuel',
    'Location filter (loc)': 'Filtre de lieu (loc)',
    'Select image type': 'Sélectionner le type d\'image',
    'Select items': 'Sélectionner les éléments',
    'Select parent category': 'Sélectionner la catégorie parente',
    'Select category level': 'Sélectionner le niveau de catégorie',
    'Select locations': 'Sélectionner les lieux',
    'Select style': 'Sélectionner le style',
    'Show description': 'Afficher la description',
    'Show image': 'Afficher l\'image',
    'Show email': 'Afficher l\'e-mail',
    'Show instructors': 'Afficher les formateurs',
    'Show long description': 'Afficher la description longue',
    'Show location info': 'Afficher les infos du lieu',
    'Show phone': 'Afficher le téléphone',
    'Wrapper padding': 'Marge intérieure du conteneur',
    'Square 1:1': 'Carré 1:1',
    'Landscape 16:9': 'Paysage 16:9',
    'Landscape 2:1': 'Paysage 2:1',
    'Landscape 3:1': 'Paysage 3:1',
    'Landscape 3:2': 'Paysage 3:2',
    'Landscape 4:1': 'Paysage 4:1',
    'Landscape 4:3': 'Paysage 4:3',
    'Portrait 2:3': 'Portrait 2:3',
    'Portrait 3:4': 'Portrait 3:4',
    'None, but use border': 'Aucun, mais utiliser une bordure',
    'Shadow/border style': 'Style ombre/bordure',
    'Adjustments': 'Ajustements',
    'Div': 'Div',
    'Double': 'Double',
    'Span': 'Span',
    'H2': 'H2',
    'H3': 'H3',
    'H4': 'H4',
    'H5': 'H5',
    'H6': 'H6',
    'P': 'P',
    'Title element': 'Élément de titre',
    'Taxonomy image': 'Image de taxonomie',
  };

  if (G[protectedEn] !== undefined) {
    return restoreBrands(G[protectedEn], tokens);
  }

  // Pattern replacements for common admin phrases
  let fr = protectedEn
    .replace(/^Show only %s$/, 'Afficher uniquement %s')
    .replace(/^Hide %s$/, 'Masquer %s')
    .replace(/^Element alignment %s$/, 'Alignement des éléments %s')
    .replace(/^Use default \(%s\)$/, 'Utiliser la valeur par défaut (%s)')
    .replace(/^Upload %s$/, 'Téléverser %s')
    .replace(/^Remove %s$/, 'Retirer %s')
    .replace(/^Copy shortcode for %s$/, 'Copier le shortcode pour %s')
    .replace(/^Expand %s$/, 'Développer %s')
    .replace(/^Image of %s$/, 'Image de %s')
    .replace(/^Image of course in %s$/, 'Image du cours à %s')
    .replace(/^Image for course in %s$/, 'Image pour le cours à %s')
    .replace(/^View course: %s$/, 'Voir le cours : %s')
    .replace(/^View courses in %s$/, 'Voir les cours à %s')
    .replace(/^Back to %s$/, 'Retour à %s')
    .replace(/^Open %s in Google Maps$/, 'Ouvrir %s dans Google Maps')
    .replace(/^Custom settings for %s$/, 'Paramètres personnalisés pour %s')
    .replace(/^%d day$/, '%d jour')
    .replace(/^%d courses$/, '%d cours')
    .replace(/^%d %s selected$/, '%d %s sélectionné')
    .replace(/^%1\$d %2\$s selected$/, '%1$d %2$s sélectionné')
    .replace(/^%1\$s has the same slug as %2\$s "%3\$s"$/, '%1$s a le même slug que %2$s « %3$s »')
    .replace(/^\+(%d) more$/, '+$1 de plus')
    .replace(/^All \(%d\)$/, 'Tous (%d)')
    .replace(/^ - page %d of %d$/, ' - page %d sur %d')
    .replace(/^– page %1\$d of %2\$d$/, '– page %1$d sur %2$d')
    .replace(/^← Back to instructors$/, '← Retour aux formateurs')
    .replace(/^← Back to course categories$/, '← Retour aux catégories de cours')
    .replace(/^← Back to course locations$/, '← Retour aux lieux de cours')
    .replace(/^-- Select page --$/, '-- Sélectionner une page --')
    .replace(/^\(100-1000px\)$/, '(100-1000px)')
    .replace(/^\* Note: This section is still under development\.$/, '* Remarque : cette section est encore en cours de développement.')
    .replace(/^– Used for troubleshooting and overview$/, '– Utilisé pour le dépannage et l\'aperçu')
    .replace(/^✓ \(can be overridden in AIOSEO Pro\)$/, '✓ (peut être remplacé dans AIOSEO Pro)')
    .replace(/^✓ \(can be overridden in SEOPress Pro\)$/, '✓ (peut être remplacé dans SEOPress Pro)');

  if (fr !== protectedEn) {
    return restoreBrands(fr, tokens);
  }

  // Full manual map for complex strings loaded from external file if present
  return null;
}

// Load full manual overrides
const manualPath = path.join(__dirname, 'fr-en-glossary.json');
const MANUAL = fs.existsSync(manualPath)
  ? JSON.parse(fs.readFileSync(manualPath, 'utf8'))
  : {};

function toFrench(key, en) {
  if (MANUAL[en] !== undefined) return MANUAL[en];
  if (MANUAL[key] !== undefined) return MANUAL[key];
  const t = translateEn(en);
  if (t !== null) return t;
  return en;
}

function buildSplit(enTranslations, enBlockEditor, locale) {
  const translations = {};
  const blockEditor = {};
  const missing = [];

  for (const [key, en] of Object.entries(enTranslations)) {
    const fr = toFrench(key, en);
    if (fr === en && !MANUAL[en] && !MANUAL[key] && translateEn(en) === null) {
      missing.push({ file: 'translations', key: key.slice(0, 80), en: en.slice(0, 80) });
    }
    translations[key] = fr;
  }

  for (const [key, en] of Object.entries(enBlockEditor)) {
    const fr = toFrench(key, en);
    if (fr === en && !MANUAL[en] && !MANUAL[key] && translateEn(en) === null) {
      missing.push({ file: 'block-editor', key: key.slice(0, 80), en: en.slice(0, 80) });
    }
    blockEditor[key] = fr;
  }

  return { translations, blockEditor, missing };
}

const { translations, blockEditor, missing } = buildSplit(enT, enB, 'fr_FR');

if (missing.length) {
  fs.writeFileSync(path.join(LANG, '_fr-still-missing.json'), JSON.stringify(missing, null, 2) + '\n');
  console.error(`Still missing ${missing.length} translations – wrote lang/_fr-still-missing.json`);
  process.exit(1);
}

fs.writeFileSync(path.join(LANG, 'translations-fr_FR.json'), JSON.stringify(translations, null, 2) + '\n');
fs.writeFileSync(path.join(LANG, 'block-editor-fr_FR.json'), JSON.stringify(blockEditor, null, 2) + '\n');

console.log('translations-fr_FR.json:', Object.keys(translations).length);
console.log('block-editor-fr_FR.json:', Object.keys(blockEditor).length);
console.log('Total unique keys:', new Set([...Object.keys(translations), ...Object.keys(blockEditor)]).size);
