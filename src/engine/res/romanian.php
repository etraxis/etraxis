<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

/**
 * Localization
 *
 * This module contains prompts translated in Romanian.
 *
 * @package Engine
 * @subpackage Localization
 * @author Dan Stoenescu
 */

$resource_romanian = array
(
    RES_SECTION_ALERTS =>
    /* 200 */
    'Toate campurile marcate ca obligatorii trebuie completate.',
    'Valoarea implicita trebuie sa fie in intervalul %1 la %2',
    'Contul este dezactivat.',
    'Contuleste blocat.',
    'Nume utilizator invalid.',
    'Numele de utilizator este deja folosit.',
    'Adresa de email invalid.',
    'Parola nu este identica.',
    'Parola trebuie sa aiba cel putin %1 caractere.',
    'Numele de proiect exista deja.',
    /* 210 */
    'Numele grupului exista deja.',
    'Sablonul cu numele sau prefixul introdus exista deja',
    'Numele sau prescurtarea starii deja exista.',
    'Numele campului deja exista.',
    'Valoarea invalida (se asteapta intreg).',
    'Valoarea intregului trebuie sa fie intre %1 si %2.',
    'Valoarea "%1" trebuie sa fie in intervalul %2 pana la %3.',
    'Valoarea maxima trebuie sa fie mai mare decat cea minima.',
    'Fisierul urcat pe server depaseste valoarea "upload_max_filesize" setata in "php.ini".',
    'Marimea fisierului urcat pe server nu poate fi mai mare de %1 Kbytes.',
    /* 220 */
    'Fisierul a fost urcat partial pe server.',
    'Niciun fisier nu a fost urcat pe server.',
    'Lipseste directorul temporar.',
    'Un atasament cu nume identic exista deja.',
    'Inregistrarea nu a fost gasita.',
    'Mai exista un filtru cu acelasi nume.',
    'Format de data invalid.',
    'Formatul de data trebuie sa fie in intervalul %1 la %2.',
    'Format de timp invalid.',
    'Formatul timpului trebuie sa fie in intervalul %1 la %2.',
    /* 230 */
    'Inregistrarea cu numele introdus exista deja.',
    'Reminder-ul cu numele introdus exista deja.',
    'Reminder-ul a fost trimis cu succes.',
    'Arata ce nume introduse exista deja.',
    NULL,
    'Eroare la scrierea fisierului pe disk.',
    'Urcarea fisierului pe server a fost oprita datorita extensiei.',
    'JavaScript trebuie activat.',
    'Acesta este un mesaj automat, te rog nu raspunde la el.',
    NULL,
    /* 240 */
    NULL,
    'View-ul nu poate avea mai mult de %1 coloane.',
    'Valoarea "%1" nu respecta formatul prestabilit.',
    'Cont neautorizat.',
    'Cont inexistent sau parola incorecta.',
    'Eroare necunoscuta.',
    'Eroare de parsare XML.',

    RES_SECTION_CONFIRMS =>
    /* 300 */
    'Sunteti sigur ca doriti sa stergeti toate sabloanele selectate?',
    'Sunteti sigur ca doriti sa stergeti acest cont?',
    'Sunteti sigur ca doriti sa stergeti acest proiect?',
    'Sunteti sigur ca doriti sa stergeti acest grup?',
    'Sunteti sigur ca doriti sa stergeti acest sablon?',
    'Sunteti sigur ca doriti sa stergeti aceasta stare?',
    'Sunteti sigur ca doriti sa stergeti acest camp?',
    NULL,
    'Sunteti sigur ca vreti sa reluati aceasta inregistrare?',
    'Sunteti sigur ca vreti sa asignati aceasta inregistrare?',
    /* 310 */
    'Sunteti sigur ca doriti sa stergeti toate filtrele selectate?',
    'Sunteti sigur ca doriti sa stergeti toate subscrierile selectate?',
    'Sunteti sigur ca doriti sa trimiteti aceasta alarma?',
    'Sunteti sigur ca doriti sa stergeti aceasta alarma?',
    'Sunteti sigur ca doriti sa iesiti?',
    'Sunteti sigur ca doriti sa stergeti aceasta inregistrare?',

    RES_SECTION_PROMPTS =>
    /* 1000 */
    'Română',
    'Autentificare',
    'OK',
    'Renunta',
    'Salveaza',
    'Inapoi',
    'Inainte',
    'Creeaza',
    'Modifica',
    'Sterge',
    /* 1010 */
    'Inregistrari',
    'Conturi',
    'Proiecte',
    'Schimba parola',
    'Campurile statusului "%1"',
    'niciunul',
    'Total:',
    'Tema',
    'Informatii despre cont',
    'Nume utilizator',
    /* 1020 */
    'Nume si prenume',
    'Email',
    'Predefinit',
    'administrator',
    'user',
    'Descriere',
    'Parola',
    'Confirmare',
    'inactiv',
    'blocat',
    /* 1030 */
    'Cont de utilizator nou',
    'Cont "%1"',
    'Informatii despre proiect',
    'Numele proiectului',
    'Data de start',
    'suspendat',
    'Proiect nou',
    'Proiect "%1"',
    'Grupuri',
    'Informatii despre grup',
    /* 1040 */
    'Numele grupului',
    'Grup nou',
    'Grup "%1"',
    'Apartenenta',
    'Altele',
    'Membrii',
    'Sabloane',
    'Informatiile sablonului',
    'Numele sablonului',
    'Prefix',
    /* 1050 */
    'Sablon nou',
    'Sablon "%1"',
    'Stare',
    'Informatii despre stare',
    'Stare',
    'Prescurtare',
    'Tipul starii',
    'initial',
    'intermediar',
    'final',
    /* 1060 */
    'Responsabil',
    'nu modifica',
    'asigneaza',
    'sterge',
    'Stare nou',
    'Stare "%1"',
    'Creaza stare intermediara',
    'Creaza stare finala',
    'Tranzitii',
    'Permisiuni',
    /* 1070 */
    'Marcheaza ca initiala',
    'Permis',
    'Campuri',
    'Informatii despre campuri',
    'Ordine',
    'Numele campului',
    'Tipul campului',
    'numar',
    'text',
    'text cu mai multe linii',
    /* 1080 */
    'Obligatoriu',
    'da',
    'nu',
    'Valoare minima',
    'Valoare maxima',
    'Lungime maxima',
    'obligatoriu',
    'Camp nou (pas %1/%2)',
    'Camp "%1"',
    'doar afisare',
    /* 1090 */
    'afisare si scriere',
    'Informatii generale',
    'ID',
    'Proiect',
    'Sablon',
    'Stare',
    'Vechime (zile)',
    'Inregistrare noua',
    'Inregistrare "%1"',
    'Inregistrarile mele',
    /* 1100 */
    'Istoric',
    'Amana',
    'Restarteaza',
    'Asigneaza',
    'Schimba starea',
    'Data',
    'Originator',
    'Inregistrarea e creata cu starea "%1".',
    'Inregistrarea e asignata lui %1.',
    'Inregistrarea e modificata.',
    /* 1110 */
    'Starea e schimbata la "%1".',
    'Inregistrarea e amanata pana la %1.',
    'Inregistrarea a fost reluata.',
    'Fisierul "%1" este atasat.',
    'Fisierul "%1" este sters.',
    'permisiune de creere inregistrari',
    'permisiune de modificare inregistrari',
    'permisiune de amanare inregistrari',
    'permisiune de reluare inregistrari',
    'permisiune de reasignare inregistrari asignate',
    /* 1120 */
    'permisiune de a schimba statusul unor inregistrari asignate',
    'permisiune de atasare fisiere',
    'permisiune de stergere fisiere',
    'Limba',
    'Adauga comentariu',
    'Comentariul este adaugat.',
    'permisiune de adaugare comentarii',
    'Comentariu',
    'Ataseaza fisier',
    'Sterge fisier',
    /* 1130 */
    'Atasament',
    'Numele atasamentului',
    'Fisierul atasat',
    'Atasamente',
    'Nr campuri.',
    'Vechimea critica',
    'Timp de suspendare ',
    'MOdificari',
    'Valoare veche',
    'Valoare noua',
    /* 1140 */
    'bifa',
    'inregistrare',
    'lista',
    'Elementele listei',
    '%1 Kb',
    'Filtre',
    'Numele filtrului',
    'Toate proiectele',
    'Toate Sabloanele',
    'Toate starile',
    /* 1150 */
    'Afiseaza inregistrarea',
    'Arata numai create de ...',
    'Arata numai asignate catre ...',
    'Arata numai deschise',
    'Subiect',
    'Cautare',
    'Parametrii de cautare',
    'Rezultatul cautarii (filtrat)',
    'Textul de cautat',
    'cauta in campuri',
    /* 1160 */
    'cauta in comentarii',
    'Stare',
    'activ',
    'Subscrieri',
    'notifica atunci cand inregistrarea este creata',
    'notifica atunci cand inregistrarea este asignata',
    'notifica atunci cand inregistrarea este modificata',
    'notifica atunci cand statusul este schimbat',
    'notifica atunci cand inregistrarea este amanata',
    'notifica atunci cand inregistrarea este relansata(resumed)',
    /* 1170 */
    'notifica atunci cand un comentariu este adaugat',
    'notifica atunci cand un fisier este adaugat',
    'notifica atunci cand un fisier este sters',
    'obligatoriu',
    'Amanat',
    'Data estimata',
    'Valoare implicita',
    'pornit',
    'oprit',
    'Rapoarte',
    /* 1180 */
    'Inregistrari deschise',
    'Creere vs Inchidere',
    'saptamana',
    'numar',
    'Clonare',
    'Inregistrarea este clonata din "%1".',
    'Iesire',
    'notifica atunci cand o inregistrare este clonata',
    'Setari',
    'Randuri pe pagina',
    /* 1190 */
    'Marcaje pe pagina',
    'Incuie',
    'Descuie',
    'Tipul grupului',
    'global',
    'local',
    'Configurare',
    'Calea locala',
    'Calea web',
    'Securitate',
    /* 1200 */
    'Lungimea minima a parolei',
    'Numarul maxim de incercari de autentificare',
    'Perioada de blocare (min)',
    'Baza de date',
    'Tipul bazei de date',
    'Oracle',
    'MySQL',
    'Microsoft SQL Server',
    'Serverul de baze de date',
    'Numele bazei de date',
    /* 1210 */
    'Utilizator de baze de date',
    'Active Directory',
    'Server LDAP',
    'Port',
    'Cauta cont',
    'Base DN',
    'Administratori',
    'Notificari email',
    'Dimensiunea maxima',
    'Depanare',
    /* 1220 */
    'Mod de depanare',
    'activat (fara date personale)',
    'activat (toate datele)',
    'Jurnale de depanare',
    'Activate',
    'Dezactivate',
    NULL,
    'permisiune a a vedea numai inregistrarile',
    'Selecteaza toate',
    'Autor',
    /* 1230 */
    'data',
    'durata',
    'arata numai pe cele amanate',
    'Numele subsrierii',
    'Evenimente',
    'Versiunea %1',
    'rolul',
    'Subscrie',
    'Sterge subscrierea',
    'Alarme',
    /* 1240 */
    'Numele alarmei',
    'Subiectul alarmei',
    'Recipientii alarmei',
    'Alarma noua',
    'Alarma "%1"',
    'permisiune de trimitere alarma',
    'Trimite',
    'Filtru nou',
    'Filtru "%1"',
    'Subscriere noua',
    /* 1250 */
    'Subscriere "%1"',
    NULL,
    'Poti introduce o legatura catre o alta inregistrare specificand "rec#" si numarul sau (ex. "rec#305").',
    'Arata numai pe cele mutate catre statusurile ...',
    'Partajeaza cu ...',
    'Exporta',
    'Subscris de altii...',
    'Subscris',
    '%1 te-a subscris la inregistrare.',
    '%1 ti-a sters subscrierea la inregistrare.',
    /* 1260 */
    'Carbon copy',
    'Statiu de stocare',
    'Atributul LDAP',
    'Sabloane',
    NULL,
    'Nume sablon',
    'New view',
    'Sablon "%1"',
    'Fara sablon',
    'Aplica',
    /* 1270 */
    'Coloane',
    NULL,
    NULL,
    NULL,
    NULL,
    'Aliniere',
    'stanga',
    'centru',
    'dreapta',
    'Serviciul va fi indisponibil de la %1 pana la %2 (%3)',
    /* 1280 */
    'Toate asignate mie',
    'Toate create de mine',
    NULL,
    'm/d/yyyy',
    'Export',
    'Inregistrari dependente',
    'Creaza inregistrare dependenta',
    'Ataseaza inregistrare dependenta',
    'Sterge inregistrare dependenta',
    'ID Inregistrare dependenta',
    /* 1290 */
    'Inregistrarea fiu "%1" este adaugata.',
    'Inregistrarea fiu "%1" este stearsa.',
    'permisiune de a adaugat inregistrari fiu',
    'permisiune de a sterge inregistrari fiu',
    'notifica atunci cand o inregistrare fiu a fost adaugata',
    'notifica atunci cand o inregistrare fiu a fost stearsa',
    'Inregistrari create',
    'Inregistrari inchise',
    'Confidential',
    'Adauga comentariu confidential',
    /* 1300 */
    'permisiune de a adauga/citi conmentarii confidentiale',
    'Comentariul confidential a fost adaugat.',
    'ID parinte',
    'dependenta',
    NULL,
    'Adauga separator',
    'Delimitator CSV',
    'Codare CSV',
    'CSV delimitator de linie',
    NULL,
    /* 1310 */
    'Activeaza filtrele',
    'Dezactiveaza filterele',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    'Arata toate detaliile',
    /* 1320 */
    'Ascunde toate detaliile',
    NULL,
    'U/E',
    'PCRE to check field values',
    'Search PCRE to transform field values',
    'Replace PCRE to transform field values',
    'Urmatoarea stare prestabilita',
    'Statusul de amanare',
    'arata toate',
    'arata doar pe cele active',
    /* 1330 */
    'Eveniment',
    NULL,
    'Acces pt vizitatori',
    'Niciunul.',
    'Grupuri globale',
    'Vizitator',
    'Importa',
    'permisiunea de a sterge inregistrari',
    NULL,
    'Limba prestabilita',
    /* 1340 */
    'Expirarea parolei (zile)',
    'Expirarea sesiunii de lucru (minute)',
    'LDAP enumeration',
    'PostgreSQL',
    'lista de indecsi',
    'list de valori',
    'Creat',
    'Marcheaza ca citite',
    'Inregistrat',
    'TLS',
    /* 1350 */
    'Comprimare',
    'U/S',
    'Comentarii',
    'Dimensiune',
    'Mod de prezentare',
    'CSV',
    'Activeaza',
    'Dezactiveaza',
    'Previzionare',
    'Proprietar',
    /* 1360 */
    'Oricine.',
    'Marcheaza ca necitit',
    'Inregistrari parinte',
);

?>
