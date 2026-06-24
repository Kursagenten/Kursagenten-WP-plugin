# Ukeplan og kalender – plan

## Status og formål

Dette dokumentet beskriver planen for to nye visninger av kurslisten:

1. **Ukeplan / timeplan** – kurs gruppert på ukedag (som danseskolene sin ukentlige timeplan)
2. **Kalender** – konkrete kursdager plottet på dato (måneds-/ukevisning)

Planen er **ikke påbegynt**. Den er skrevet etter en datakartlegging av tre API-eksempler
og skal kunne plukkes opp senere. Design er bevisst utsatt – datagrunnlag og
klassifisering er prioritert først.

> **Viktig avhengighet (uavklart):**
> Det er meldt inn en feil i Kursagenten der et kurs delt inn i **perioder**
> (f.eks. «Sommer» og «Vinter») kommer ut av API-et som **to separate schedules /
> påmeldinger**. Hvordan utvikler løser dette er ennå ukjent. Se eget avsnitt
> [«Periode-problemet»](#periode-problemet-uavklart) – flere designvalg avhenger av utfallet.

---

## Datakartlegging (oppsummert)

Tre eksempler ble analysert:

| Kilde | Dato-kvalitet | Konklusjon |
|---|---|---|
| Ewa Trela (gammel) | 0 av 29 schedules hadde `firstCourseDate` | For svakt – ukedag måtte gjettes fra fritekst |
| Testkurs 232799 | `daySchedules` med 16 konkrete dager | Godt nok for både ukeplan og kalender |
| Hundekurs 233032 | Perioder, per-dag-variasjon, unntak | Rikest – avdekker alle edge-caser |

### Felt vi får fra `daySchedules` (per dag)

```json
{ "date": "2026-06-24T00:00:00", "startTime": "10", "endTime": "12",
  "description": "Sommer", "instructors": ["Henning Hagen"],
  "locationRooms": ["Escens HQ"] }
```

### Slik kommer «perioder» ut i dag (kurs 233032, lokasjon 233032)

| Schedule | `description` | Periode | Slots (avledet) |
|---|---|---|---|
| 2904211 | «Sommer» | 24.06–28.10 | Man 08–15:30 (Millie/Garasjen), Ons 10–12 (Henning/Escens HQ) |
| 2904249 | «Vinter» | 04.11–21.04 | Tor 13–14 hver uke (Henning/Garasjen), Tir 11–12 annenhver uke (Tone/Escens HQ) |

Dette matcher admin-UI-et «Del kurset inn i perioder» 1:1.

---

## Kjente problemer som må løses først

### 1. Tidsparsing er feil (reell bug) — HØY prioritet

`daySchedules` bruker blandet format: `"10"`, `"08"`, `"13"` (hele timer) og
`"1530"`, `"1100"`, `"0800"` (HHMM).

Dagens parser i `includes/api/api_day_schedules.php`
(`kursagenten_format_day_schedule_time`) venstre-padder alt til 4 sifre:

- `"08"` → `"0008"` → **`00:08`** (skal være `08:00`)
- `"10"` → `"0010"` → **`00:10`** (skal være `10:00`)
- `"1530"` → `15:30` ✅

**Fix:** 1–2 sifre tolkes som hele timer (minutt `00`); 3–4 sifre venstre-paddes til HHMM.
Påvirker også dagens «X dager»-popup, så bør fikses uavhengig av ukeplan/kalender.

```php
// Forslag
function kursagenten_format_day_schedule_time($time_value) {
    if (!is_string($time_value) && !is_int($time_value)) {
        return '';
    }
    $digits = preg_replace('/\D+/', '', (string) $time_value);
    if ($digits === '') {
        return (string) $time_value;
    }
    $len = strlen($digits);
    if ($len <= 2) {
        // Hele timer: "8" / "08" / "13" -> HH:00
        $hour = str_pad($digits, 2, '0', STR_PAD_LEFT);
        return $hour . ':00';
    }
    if ($len === 3) {
        // "930" -> 09:30
        $digits = '0' . $digits;
    }
    if ($len > 4) {
        return (string) $time_value; // ukjent format, la stå
    }
    return substr($digits, 0, 2) . ':' . substr($digits, 2, 2);
}
```

### 2. `schedule.description` (periodenavn) lagres ikke

«Sommer»/«Vinter» ligger i `schedule['description']` (og gjentas per daySchedule).
Bør lagres som `ka_course_period_name` – nyttig som overskrift i begge visninger.

### 3. daySchedules lagres ikke i WP

I dag lagres kun `ka_course_day_schedules_count`; selve dagene hentes on-demand via
AJAX (popup). For **kalender** trengs datoene i WP. For **unntak** (se under) er det
helt avgjørende at vi bruker faktiske datoer, ikke genererer dem.

**Anbefaling:** hybrid – lagre en kompakt, normalisert dagsliste ved sync, behold full
detalj via AJAX der det trengs.

```json
// ka_course_calendar_days (kompakt)
[ { "d": "2026-06-24", "s": "10:00", "e": "12:00", "r": "Escens HQ" },
  { "d": "2026-06-29", "s": "08:00", "e": "15:30", "r": "Garasjen" } ]
```

### 4. Unntak håndteres implisitt

Unntak (f.eks. helligdager 25.12, 26.12 …) er bare **utelatt** fra `daySchedules`.
Derfor: **aldri** generer datoer fra `first/last + ukedag` – bruk daySchedules direkte.

### 5. Datoer uten tid

Schedule 2885956 har 21 dager med **kun `date`** (ingen tid/instruktør/rom).
Klassifisering: kalender = mulig (dato uten tid), ukeplan = nei.

### 6. Schedules uten `id`

Gamle/fritekst-schedules («Tirsdager 17-20», «Meld interesse») har ingen `id` →
`schedule_id = 0`. Klassifiseres som ikke egnet for begge visninger.

### 7. Instruktør/rom varierer per dag

Noen dager har to instruktører, andre én. Slot-gruppering bør skje på
`(ukedag, starttid, sluttid, rom)` og behandle instruktør som et sett som kan variere.

---

## Periode-problemet (uavklart)

Et kurs delt i perioder («Sommer»/«Vinter») kommer i dag som **to separate
`ka_coursedate`-poster** (én per schedule), fordi pluginen lager én coursedate per
`(location_id, schedule_id)`. Det gir to påmeldinger der det egentlig er ett kurs.

Feilen er meldt til Kursagenten-utvikler. **Avhengig av hvordan den løses**, må vi velge
strategi:

| Utfall fra Kursagenten | Konsekvens for oss |
|---|---|
| **A. Perioder slås sammen til én schedule** med `periods[]`-struktur | Vi parser perioder under én coursedate. Enklest for påmelding. Klassifisering må håndtere flere perioder per coursedate. |
| **B. Beholder separate schedules, men merker dem med felles `groupId`/`parentScheduleId`** | Vi grupperer coursedates på groupId i visning, men beholder dem som egne poster. |
| **C. Ingen endring** (forblir to schedules) | Vi må selv gruppere på heuristikk: samme `location_id` + tilstøtende datointervaller + samme kurs. Skjørt – kun for visning, ikke påmelding. |

**Beslutning utsatt** til utfallet er kjent. Inntil da bygges klassifisering og visning
slik at den fungerer per schedule, men med et `period_name`-felt klart, slik at en
gruppering kan legges på toppen senere uten omskriving.

---

## Foreslått arkitektur

### Pipeline

```
API schedule
  -> normaliser tid (fix #1)
  -> trekk ut slots: grupper daySchedules på (ukedag, start, slutt, rom)
  -> avled frekvens (hver uke / annenhver) fra datogap
  -> klassifiser: calendar_eligible / weekplan_eligible (+ reason)
  -> lagre meta (eligibility, period_name, kompakt dagsliste, slots)
  -> ukeplan/kalender-visning filtrerer på *_eligible
```

### Nye meta-felt (per `ka_coursedate`)

| Meta-nøkkel | Type | Beskrivelse |
|---|---|---|
| `ka_course_period_name` | string | «Sommer»/«Vinter» fra `schedule.description` |
| `ka_schedule_calendar_eligible` | `yes`/`no` | Kan plottes på dato |
| `ka_schedule_weekplan_eligible` | `yes`/`no` | Kan vises som ukedag-rad |
| `ka_schedule_eligibility` | JSON | Forklaring + reason-koder (feilsøking) |
| `ka_schedule_slots` | JSON | Avledede slots (ukedag, tid, rom, frekvens, antall, fra/til) |
| `ka_course_calendar_days` | JSON | Kompakt dagsliste for kalender |
| `ka_schedule_view_override` | `force_include`/`force_exclude`/`` | Manuell overstyring (valgfri) |

### Eksempel `ka_schedule_slots` (schedule «Sommer»)

```json
[
  { "weekday": 1, "weekday_label": "Mandag", "start": "08:00", "end": "15:30",
    "room": "Garasjen", "instructors": ["Millie Hagen"],
    "frequency": "weekly", "count": 18, "from": "2026-06-29", "to": "2026-10-26" },
  { "weekday": 3, "weekday_label": "Onsdag", "start": "10:00", "end": "12:00",
    "room": "Escens HQ", "instructors": ["Henning Hagen"],
    "frequency": "weekly", "count": 19, "from": "2026-06-24", "to": "2026-10-28" }
]
```

---

## Klassifiseringsregler

### Kalender-egnethet

```
calendar_eligible = yes hvis:
  - minst 1 daySchedule med gyldig dato, ELLER
  - firstCourseDate + gyldig tid

calendar_eligible = no hvis:
  - ingen datoer
  - schedule uten id og uten daySchedules
```

### Ukeplan-egnethet

```
weekplan_eligible = yes hvis:
  - minst 1 slot med stabil ukedag OG gyldig tid

weekplan_eligible = no hvis:
  - kun dato-only daySchedules (ingen tid)
  - daglig mønster uten fast ukedag (vurderes som "blokk", ikke ukeplan)
  - "Ta kontakt for avtale" / privatundervisning
  - fritekst-schedule uten id
```

### Reason-koder (lagres i `ka_schedule_eligibility`)

`ok`, `no_dates`, `no_time`, `date_only`, `contact_for_agreement`,
`no_schedule_id`, `daily_block`, `forced_include`, `forced_exclude`.

---

## Byggekloss-funksjoner (skisse)

To rene hjelpefunksjoner i en ny fil, f.eks.
`includes/api/api_schedule_classification.php`:

```php
/**
 * Normalize "HHMM"/"HH"/"H" style time tokens to "HH:MM".
 * (Erstatter/komplementerer kursagenten_format_day_schedule_time.)
 */
function kursagenten_normalize_time_token($value): string { /* fix #1 */ }

/**
 * Group a schedule's daySchedules into recurring slots.
 *
 * @return array<int, array{weekday:int, start:string, end:string, room:string,
 *                          instructors:string[], frequency:string, count:int,
 *                          from:string, to:string}>
 */
function kursagenten_extract_schedule_slots(array $schedule): array { /* grupper + frekvens */ }

/**
 * Classify a schedule for calendar/weekplan views.
 *
 * @return array{calendar:array, weekplan:array, slots:array, flags:array}
 */
function kursagenten_classify_schedule_for_views(array $schedule, array $location): array { }
```

Frekvens avledes fra median-gap mellom påfølgende datoer i samme slot:
~7 dager = `weekly`, ~14 = `biweekly`, ellers `irregular`.

---

## Faseplan

### Fase 0 – Forberedelse (kan gjøres nå, uavhengig av periode-bug)
- [ ] Fiks tidsparsing (#1) + test mot 233032-dataene
- [ ] Lagre `ka_course_period_name` i sync (#2)
- [ ] Bestem lagringsstrategi for daySchedules (hybrid anbefalt, #3)

### Fase 1 – Klassifisering
- [ ] `kursagenten_normalize_time_token()`
- [ ] `kursagenten_extract_schedule_slots()`
- [ ] `kursagenten_classify_schedule_for_views()`
- [ ] Skriv eligibility-meta i sync (rundt linje 571–600 i `api_course_sync.php`)
- [ ] Backfill-rutine for eksisterende kurs (re-sync)

### Fase 2 – Ukeplan
- [ ] Ny `list_type` / visning «ukeplan» (jf. eksisterende list-types)
- [ ] Query: filtrer `ka_schedule_weekplan_eligible = yes`
- [ ] Render: grupper på ukedag → slot (rom, tid, instruktør, frekvens, «Meld deg på»)
- [ ] Gjenbruk eksisterende filtre (sted, kategori, instruktør, språk)

### Fase 3 – Kalender
- [ ] Start med timeplan-kalender (ukedager som kolonner)
- [ ] Deretter ekte månedskalender fra `ka_course_calendar_days`
- [ ] Håndter unntak (allerede utelatt i data) og dato-only-kurs

### Fase 4 – Periode-gruppering (når Kursagenten-bug er avklart)
- [ ] Implementer valgt strategi (A/B/C fra tabellen over)
- [ ] Slå sammen «Sommer»+«Vinter» i visning (og evt. påmelding)

---

## Åpne spørsmål

1. Hvordan løser Kursagenten periode-bugen? (styrer Fase 4)
2. Skal helkurs (08–16 daglig) vises i ukeplan som «blokk», eller kun i kalender?
3. Skal kurs uten ledige plasser / uten påmelding vises (med/uten knapp)?
4. Trengs manuell override per coursedate, eller holder automatikk?
5. Skal `ka_schedule_slots` indekseres som egne meta (`weekday`, `start_time`) for
   raskere sortering i ukeplan?

---

## Relaterte filer

- `includes/api/api_course_sync.php` – sync, schedule-loop (linje ~515), meta (~571)
- `includes/api/api_day_schedules.php` – tidsparsing, daySchedules-henting
- `includes/api/api_connection.php` – API-endepunkt (`includeFullSchedules=true`)
- `public/templates/list-types/` – eksisterende list-types (mal for ny visning)
- `includes/options/coursedesign.php` – registrering av list-types i admin
