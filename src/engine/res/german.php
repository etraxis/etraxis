<?php

/**
 * Localization
 *
 * This module contains prompts translated in German.
 * All the prompts are in ISO-8859-1 encoding.
 *
 * @package Engine
 * @subpackage Localization
 * @author Daniel Jungbluth
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2007-2010 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Daniel Jungbluth        2007-09-09      new-577: German localization.
//  Daniel Jungbluth        2007-09-09      new-563: Custom separators inside fields set.
//  Daniel Jungbluth        2007-09-12      new-576: [SF1788286] Export to CSV
//  Daniel Jungbluth        2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Daniel Jungbluth        2007-10-02      new-513: Apply current filter set to search results.
//  Daniel Jungbluth        2007-10-29      new-564: Filters set.
//  Daniel Jungbluth        2007-11-05      new-562: Ability to show only last values of any state.
//  Daniel Jungbluth        2007-11-07      new-571: View should show all records of current filters set.
//  Daniel Jungbluth        2007-11-19      bug-624: dbx_error(): Too many tables; MySQL can only use 61 tables in a join
//  Daniel Jungbluth        2007-11-19      new-599: Separated "Age" in custom views.
//  Daniel Jungbluth        2007-11-19      new-548: Custom links in text fields.
//  Daniel Jungbluth        2007-11-19      new-623: Default state in states list.
//  Daniel Jungbluth        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Daniel Jungbluth        2007-11-29      new-637: Subject of notifications should contain subject of records.
//  Daniel Jungbluth        2007-11-29      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Daniel Jungbluth        2007-12-27      new-659: Set default language
//  Artem Rodygin           2007-12-30      bug-660: [SF1860788] PHP4 html_entity_decode() is not working
//  Daniel Jungbluth        2008-01-21      new-666: Buttons "Previous" & "Next" on record view page.
//  Daniel Jungbluth        2008-01-28      new-531: LDAP Guest users
//  Daniel Jungbluth        2008-01-31      new-601: [SF1814666] Export and Import Templates
//  Daniel Jungbluth        2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Daniel Jungbluth        2008-03-03      bug-681: Update configuration page with new options.
//  Daniel Jungbluth        2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Daniel Jungbluth        2008-05-01      new-715: Show creation time in the list of records.
//  Daniel Jungbluth        2008-08-16      new-729: [SF2008579] Mark all records as read
//  Daniel Jungbluth        2009-01-12      new-749: Guest access for unauthorized users.
//  Daniel Jungbluth        2009-01-12      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-04-12      bug-806: German translation causes two ambiguous "zuruck" buttons.
//  Artem Rodygin           2009-04-26      new-817: Field permissions dialog refactoring.
//  Artem Rodygin           2009-04-26      new-818: Change buttons layout on viewing record page.
//  Artem Rodygin           2009-10-12      new-848: LDAP TLS support.
//  Artem Rodygin           2009-10-12      new-837: Replace "Groups" with "Global groups" in main menu.
//  Artem Rodygin           2010-02-01      new-902: Transparent gzip compression of attachments
//--------------------------------------------------------------------------------------------------

$resource_german = array
(
    RES_SECTION_ALERTS =>
    /* 200 */
    'Die gekennzeichneten Felder [*] sind Pflichtfelder.',
    'Der vorgegebene Wert muss im Bereich von %1 bis %2 liegen.',
    'Konto ist deaktiviert.',
    'Konto ist gesperrt.',
    'Ungültiger Benutzername.',
    'Ein Konto mit diesem Benutzernamen existiert bereits.',
    'Ungültige Email-Adresse.',
    'Die Passwörter stimmen nicht überein.',
    'Das Passwort muss mindestens %1 Zeichen lang sein.',
    'Ein Projekt mit diesem Namen existiert bereits.',
    /* 210 */
    'Eine Gruppe mit diesem Namen existiert bereits.',
    'Eine Anfrageart mit diesem Namen oder Präfix existiert bereits.',
    'Ein Status mit diesem Namen oder Abkürzung existiert bereits.',
    'Ein Zusatz mit diesem Namen existiert bereits.',
    'Ungültiger Ganzzahlwert.',
    'Ganzzahlwert muss im Bereich von %1 bis %2 liegen.',
    'Der Wert von "%1" muss im Bereich von %2 bis %3 liegen.',
    'Das Maximum muss größer als das Minimum sein.',
    'Die hochgeladene Datei überschreitet die in der Direktive "upload_max_filesize" in "php.ini" festgelegte Dateigröße.',
    'Die Dateigröße darf %1 Kbytes nicht überschreiten.',
    /* 220 */
    'Die Datei wurde nur teilweise hochgeladen.',
    'Keine Datei hochgeladen.',
    'Temporärer Ordner fehlt.',
    'Ein Anhang mit diesem Namen existiert bereits.',
    'Anfrage nicht gefunden.',
    'Ein Filter mit diesem Namen existiert bereits.',
    'Ungültige Datumsangabe.',
    'Das Datum muss zwischen %1 und %2 liegen.',
    'Ungültige Uhrzeitangabe.',
    'Die Uhrzeit muss zwischen %1 und %2 liegen.',
    /* 230 */
    'Ein Abonnement mit diesem Namen existiert bereits.',
    'Eine Erinnerung mit diesem Namen existiert bereits.',
    'Die Erinnerung ist erfolgreich zugestellt worden.',
    'Eine Ansicht mit diesem Namen existiert bereits.',
    'Eine Spalte mit diesem Namen existiert bereits.',
    'Datei konnte nicht geschrieben werden.',
    'Das Hochladen wurde von einer Erweiterung beendet.',
    'JavaScript muss aktiviert sein.',
    'Bitte antworten Sie nicht auf diese automatisch generierte Nachricht.',
    'Die angegebene Unteranfrage existiert bereits.',
    /* 240 */
    'Eine Filtergruppe mit diesem Namen existiert bereits.',
    'Maximal %1 Spalten pro Ansicht erlaubt.',
    'Regulärer Ausdruck in "%1" ist ungültig.',
    'Benutzer ist nicht autorisiert.',
    'Unbekannter Benutzername oder falsches Passwort.',
    'Unbekannte Authentifizierungsart.',
    'Unbekannter Fehler.',
    'XML-Parser fehlerhaft.',
    'Verbindungsfehler zur Datenbank.',

    RES_SECTION_CONFIRMS =>
    /* 300 */
    'Möchten Sie alle ausgewählten Ansichten wirklich löschen?',
    'Möchten Sie dieses Konto wirklich löschen?',
    'Möchten Sie dieses Projekt wirklich löschen?',
    'Möchten Sie diese Gruppe wirklich löschen?',
    'Möchten Sie diese Anfrageart wirklich löschen?',
    'Möchten Sie diesen Status wirklich löschen?',
    'Möchten Sie diesen Zusatz wirklich löschen?',
    'Möchten Sie diese Anfrage wirklich hinauszögern?',
    'Möchten Sie diese Anfrage wirklich wiederaufnehmen?',
    'Möchten Sie diese Anfrage wirklich jemandem zuordnen?',
    /* 310 */
    'Möchten Sie wirklich alle ausgewählten Filter löschen?',
    'Möchten Sie wirklich alle ausgewählten Abonnements löschen?',
    'Möchten Sie diese Erinnerung wirklich senden?',
    'Möchten Sie diese Erinnerung wirklich löschen?',
    'Möchten Sie diese Spalte wirklich löschen?',
    'Möchten Sie wirklich beenden?',
    'Möchten Sie alle ausgewählten Filtergruppen löschen?',
    'Möchten Sie diese Anfrage wirklich löschen?',

    RES_SECTION_PROMPTS =>
    /* 1000 */
    'Deutsch',
    'Anmelden',
    '   OK    ',
    'Abbrechen',
    'Speichern',
    'Zurück',
    'Weiter',
    'Erstellen',
    'Ändern',
    'Löschen',
    /* 1010 */
    'Anfragen',
    'Konten',
    'Projekte',
    'Passwort ändern',
    'Zusätze von Status "%1"',
    'keiner',
    'Gesamt:',
    'Passwort speichern',
    'Kontoinformationen',
    'Benutzername',
    /* 1020 */
    'Vollständiger Name',
    'Email-Adresse',
    'Rechte',
    'Administrator',
    'Benutzer',
    'Beschreibung',
    'Passwort',
    'Passwort wiederholen',
    'deaktiviert',
    'gesperrt',
    /* 1030 */
    'Neues Konto',
    'Konto "%1"',
    'Projektinformationen',
    'Projektname',
    'Anfangszeit',
    'ausgesetzt',
    'Neues Projekt',
    'Projekt "%1"',
    'Gruppen',
    'Gruppeninformationen',
    /* 1040 */
    'Gruppenname',
    'Neue Gruppe',
    'Gruppe "%1"',
    'Mitgliedschaft',
    'Andere',
    'Mitglieder',
    'Anfragearten',
    'Informationen über Anfrageart',
    'Name der Anfrageart',
    'Präfix',
    /* 1050 */
    'Neue Anfrageart',
    'Anfrageart "%1"',
    'Status',
    'Statusinformationen',
    'Statusname',
    'Abkürzung',
    'Statusart',
    'Startstatus',
    'Zwischenstatus',
    'Endstatus',
    /* 1060 */
    'Bearbeiter(in)',
    'beibehalten',
    'zuordnen',
    'abziehen',
    'Neuer Status',
    'Status "%1"',
    'Status erstellen',
    'Endstatus erstellen',
    'Übergänge',
    'Berechtigungen',
    /* 1070 */
    'Als Startstatus',
    'Erlaubt',
    'Zusätze',
    'Zusatzinformationen',
    'Reihenfolge',
    'Zusatzname',
    'Zusatzart',
    'Zahl',
    'Text',
    'Mehrzeiliger Text',
    /* 1080 */
    '*',
    'Ja',
    'Nein',
    'Min. Wert',
    'Max. Wert',
    'Max. Länge',
    '*',
    'Neuer Zusatz (Schritt %1/%2)',
    'Zusatz "%1"',
    'nur lesen',
    /* 1090 */
    'lesen und schreiben',
    'Allgemeine Informationen',
    'ID',
    'Projekt',
    'Art der Anfrage',
    'Status',
    'Alter',
    'Neue Anfrage (Schritt %1/%2)',
    'Anfrage "%1"',
    'Los!',
    /* 1100 */
    'Historie',
    'Verzögern',
    'Wiederaufnehmen',
    'Zuweisen',
    'Status wechseln',
    'Zeitmarke',
    'Ersteller(in)',
    'Die Anfrage wird im Status "%1" erstellt.',
    'Die Anfrage wird %1 zugewiesen.',
    'Die Anfrage wird geändert.',
    /* 1110 */
    'Der Status wechselt nach "%1".',
    'Die Anfrage wird bis zum %1 verzögert.',
    'Die Anfrage wird wiederaufgenommen.',
    'Datei "%1" wird angehangen.',
    'Datei "%1" wird entfernt.',
    'Berechtigt, um Anfragen zu erstellen',
    'Berechtigt, um Anfragen zu ändern',
    'Berechtigt, um Anfragen hinauszuzögern',
    'Berechtigt, um Anfragen wiederaufzunehmen',
    'Berechtigt, um bereits zugeordnete Anfragen neu zuzuordnen',
    /* 1120 */
    'Berechtigt zum Ändern des Status von zugeordneten Anfragen',
    'Berechtigt, um Dateien anzuhängen',
    'Berechtigt, um Dateien zu entfernen',
    'Sprache',
    'Kommentar hinzufügen',
    'Kommentar hinzugefügt.',
    'Berechtigt zum Hinzufügen von Kommentaren',
    'Kommentar',
    'Datei anhängen',
    'Datei entfernen',
    /* 1130 */
    'Anhang',
    'Name des Anhangs',
    'Anzuhängende Datei',
    'Anhänge',
    'Keine Zusätze.',
    'Kritisches Alter',
    'Einfrieren nach Ende',
    'Änderungen',
    'Alter Wert',
    'Neuer Wert',
    /* 1140 */
    'Ankreuzfeld',
    'Anfrage',
    'Auswahlliste',
    'Listeneinträge',
    '%1 Kb',
    'Filter',
    'Filtername',
    'Alle Projekte',
    'Alle Anfragearten',
    'Jeder Status',
    /* 1150 */
    'Anfrage anzeigen',
    'Nur anzeigen, wenn erstellt von ...',
    'Nur anzeigen, wenn zugewiesen an ...',
    'Nur Offene anzeigen',
    'Betreff',
    'Suche',
    'Suchparameter',
    'Suchergebnis (gefiltert)',
    'Zu suchender Text',
    'In Zusätzen suchen',
    /* 1160 */
    'In Kommentaren suchen',
    'Status',
    'Aktiv',
    'Abonnements',
    'Benachrichtigen, wenn Anfrage erstellt wird',
    'Benachrichtigen, wenn Anfrage jemandem zugeordnet wird',
    'Benachrichtigen, wenn Anfrage verändert wird',
    'Benachrichtigen, wenn Status gewechselt wird',
    'Benachrichtigen, wenn Anfrage hinausgezögert wird',
    'Benachrichtigen, wenn Anfrage wiederaufgenommen wird',
    /* 1170 */
    'Benachrichtigen, wenn Kommentar hinzugefügt wird',
    'Benachrichtigen, wenn Datei angehangen wird',
    'Benachrichtigen, wenn Datei entfernt wird',
    '*',
    'Hinausgezögert',
    'Fälligkeitstermin',
    'Vorgabewert',
    'an',
    'aus',
    'Metriken',
    /* 1180 */
    'Offene Anfragen',
    'Offen/Abgeschlossen',
    'Woche',
    'Anzahl',
    'Klonen',
    'Anfrage wird geklont von "%1".',
    'Abmelden',
    'Benachrichtigen, wenn Anfrage geklont wird',
    'Einstellungen',
    'Zeilen pro Seite',
    /* 1190 */
    'Lesezeichen pro Seite',
    'Sperren',
    'Freischalten',
    'Gruppenart',
    'global',
    'lokal',
    'Konfiguration',
    'Lokaler Pfad',
    'Basis-URL',
    'Sicherheit',
    /* 1200 */
    'Minimale Passwortlänge',
    'Maximale Anzahl von Anmeldeversuchen',
    'Sperren nach (Minuten)',
    'Datenbank',
    'Datenbankart',
    'Oracle',
    'MySQL',
    'Microsoft SQL Server',
    'Datenbankserver',
    'Datenbankname',
    /* 1210 */
    'Datenbankbenutzer',
    'Active Directory',
    'LDAP-Server',
    'Portnummer',
    'Konto suchen',
    'Basis-DN',
    'Administratoren',
    'Email-Benachrichtigungen',
    'Maximale Größe',
    'Debug',
    /* 1220 */
    'Debug-Modus',
    'aktiviert (ohne private Daten)',
    'aktiviert (alle Daten)',
    'Debug-Log',
    'Aktiviert',
    'Deaktiviert',
    '%1 Min.',
    'Berechtigt, um Anfragen nur anzuzeigen',
    'Alles auswählen',
    'Verfasser(in)',
    /* 1230 */
    'Datum',
    'Bearbeitungsdauer',
    'Nur Hinausgezögerte anzeigen',
    'Abonnementname',
    'Ereignisse',
    'Version %1',
    'Rolle',
    'Abonnieren',
    'Abbestellen',
    'Erinnerungen',
    /* 1240 */
    'Erinnerungsname',
    'Erinnerungsbetreff',
    'Erinnerungsempfänger',
    'Neue Erinnerung (Schritt %1/%2)',
    'Erinnerung "%1"',
    'Berechtigt, um Erinnerungen zu senden',
    'Senden',
    'Neuer Filter',
    'Filter "%1"',
    'Neues Abonnement',
    /* 1250 */
    'Abonnement "%1"',
    'Ihr LDAP-Passwort',
    'Man kann Verweise zu anderen Anfragen einfügen, indem man "rec#" und die entsprechende Nummer (z.B. "rec#305") angibt.',
    'Nur Anfragen anzeigen, die gewechselt sind nach Status ...',
    'Gemeinsam verwenden mit ...',
    'Exportieren',
    'Andere abonnieren...',
    'Abonniert',
    '%1 hat für Sie die Anfrage abonniert.',
    '%1 hat die Anfrage abbestellt.',
    /* 1260 */
    'CC',
    'Speicherort',
    'LDAP-Attribut',
    'Ansichten',
    'Ansichtinformationen',
    'Ansichtname',
    'Neue Ansicht (Schritt %1/%2)',
    'Ansicht "%1"',
    'Keine Ansicht',
    'Setzen',
    /* 1270 */
    'Spalten',
    'Spalteninformationen',
    'Spaltenname',
    'Neue Spalte',
    'Spalte "%1"',
    'Ausrichtung',
    'links',
    'zentriert',
    'rechts',
    'Dienst ist nicht verfügbar von %1 bis %2 (%3)',
    /* 1280 */
    'Alle mir Zugeordneten',
    'Alle von mir Erstellten',
    'Alle abwählen',
    'd.m.yyyy',
    'Exportieren',
    'Unteranfrage',
    'Unteranfrage erstellen',
    'Unteranfrage hinzufügen',
    'Unteranfrage entfernen',
    'Unteranfrage-ID',
    /* 1290 */
    'Unteranfrage wird "%1" hinzugefügt.',
    'Unteranfrage wird von "%1" entfernt.',
    'Berechtigt, um Unteranfragen hinzuzufügen',
    'Berechtigt, um Unteranfragen zu entfernen',
    'Benachrichtigen, wenn Unteranfrage hinzugefügt wird',
    'Benachrichtigen, wenn Unteranfrage entfernt wird',
    'Erstellte Anfragen',
    'Geschlossene Anfragen',
    'Vertraulich',
    'Vertraulichen Kommentar hinzufügen',
    /* 1300 */
    'Berechtigt, um vertrauliche Kommentare hinzuzufügen und zu ändern',
    'Vertraulicher Kommentar wird hinzugefügt.',
    'Oberanfrage-ID',
    'Abhängigkeit',
    'versteckt',
    'Trennlinie hinzufügen',
    'CSV-Trennzeichen',
    'CSV-Kodierung',
    'CSV-Zeilenumbruch',
    'Suchergebnis (ungefiltert)',
    /* 1310 */
    'Filter aktivieren',
    'Filter deaktivieren',
    'Aktuelle Filtergruppe',
    'Filtergruppe speichern',
    'Filtergruppen',
    'Name der Filtergruppe',
    'Filtergruppe "%1"',
    'Aktuelle Ansicht',
    'Ansicht speichern',
    'Alle erweitern',
    /* 1320 */
    'Alle reduzieren',
    'Auf Standardwerte zurücksetzen',
    'L.E.',
    'Erlaubte Werte als regulärer Ausdruck (PCRE)',
    'Suchmuster (PCRE) zum Umwandeln von Zusatzinformationen',
    'Ersetzungsmuster (PCRE) zum Umwandeln von Zusatzinformationen',
    'Standardmäßig nächster Zustand',
    'Zurückgestellte Anfragen',
    'Alle anzeigen',
    'Nur Aktive anzeigen',
    /* 1330 */
    'Ereignis',
    'Kein Filter gesetzt',
    'Gastzugriff',
    'Keiner.',
    'Global gruppen',
    'Gast',
    'Importieren',
    'Berechtigt, um Anfragen zu löschen',
    'Authentisierungstyp',
    'Voreinstellung Sprache',
    /* 1340 */
    'Maximales Passwortalter (Tage)',
    'Sitzungsende nach Inaktivität (Minuten)',
    'LDAP-Aufzählung',
    'PostgreSQL',
    'Liste an Indizes',
    'Liste an Werten',
    'Erstellt am',
    'Als gelesen markieren',
    'Registriert',
    'TLS',
    /* 1350 */
    'Compression',  // TBT
);

?>
