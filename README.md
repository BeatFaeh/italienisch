# Italienische Lernkarten

PHP/MariaDB-Lernkarten-App, abgeleitet aus dem Projekt `buddha(4).zip` und angepasst an die Tabelle `italienisch_woerter_und_saetze`.

## Funktionen

- Deutsch → Italienisch und Italienisch → Deutsch
- Wörter oder ganze Sätze lernen
- Filter nach Lektion
- Suche nach ID, deutschem/italienischem Wort oder Satz
- Zufallskarten
- Multiple Choice
- Prüfung mit bis zu 100 zufälligen Fragen
- Druckansicht / PDF über die Browser-Druckfunktion
- Administration: Datensätze anlegen, bearbeiten und löschen
- Administrationspasswort ändern
- Responsive CSS im Italien-Look

## Installation

1. Dateien auf den Webserver kopieren.
2. Datenbank `italienisch_db` anlegen.
3. `database/italienisch_woerter_und_saetze.sql` importieren, falls die Tabelle noch nicht existiert.
4. In `config/database.php` Benutzername und Passwort anpassen oder die Umgebungsvariablen `ITALIENISCH_DB_HOST`, `ITALIENISCH_DB_USERNAME`, `ITALIENISCH_DB_PASSWORD`, `ITALIENISCH_DB_NAME` setzen.
5. PHP 8.1+ mit `mysqli` verwenden.

Die App legt bei Bedarf zusätzlich die Tabelle `italienisch_einstellungen` für das Administrationspasswort an.

## Datenmodell

- `wort_de`: deutsches Wort
- `wort_it`: italienisches Wort
- `satz_de`: deutscher Beispielsatz
- `satz_it`: italienischer Beispielsatz
- `lektion`: Lektionsnummer

Hinweis: Für den Wort-Lernmodus sind `wort_de` und `wort_it` erforderlich. Für den Satz-Lernmodus müssen `satz_de` und `satz_it` gefüllt sein.

## Prüfungen
Prüfungen verwenden immer vollständige Sätze (`satz_de` / `satz_it`), unabhängig vom auf der Startseite gewählten Lernkartentyp.

## Datenbank-ID
Die Datenbank-ID wird in der Lernkartenansicht, bei Prüfungsfragen, in der Druck-/PDF-Ansicht und in der Administration sichtbar ausgegeben.

## Admin-Suche
Im Administrationsbereich kann nach Datenbank-ID, deutschem Wort und italienischem Wort gesucht werden. Die Suche kann auf ein Feld eingeschränkt oder kombiniert verwendet werden.

## Verben (Erweiterung 18.08.2026)
- Neue Tabelle `italienisch_verben` mit ID, Verb, Präsens, Perfekt, Futur, Imperativ und Endung.
- Geschützte Übersicht über `index.php?action=verben`; verwendet dieselbe Admin-Anmeldung.
- Administration: Verben neu erfassen, suchen, bearbeiten und löschen.
- Gemeinsame Druck-/PDF-Ansicht über `index.php?action=verben-pdf` (A4 quer).
- Tabellenanlage erfolgt bei Bedarf automatisch über `database/schema.php`; die mitgelieferte SQL-Struktur liegt zusätzlich unter `database/italienisch_verben.sql`.

## Erweiterung Grammatik / Verben (18.08.2026)
- Verb-Endung im Admin-Bereich als Auswahl: are, ere, ire, unregelmässig.
- Öffentlicher Link `grammatik/` mit Stichwortsuche.
- Admin-CRUD für Grammatikeinträge inkl. Feld `pdf`.
- PDF-Upload in `grammatik/` (max. 25 MB), Umbenennen und Löschen.
- Beim Umbenennen/Löschen einer Grammatik-PDF werden passende DB-Verweise im Feld `pdf` automatisch angepasst bzw. geleert.
- `database/italienisch_grammatik.sql` liegt als Fallback-/Referenzstruktur bei. `database/schema.php` legt die Tabelle bei Neuinstallationen ebenfalls an.
- Die Repository-Schicht erkennt für das Stichwort auch die Spaltennamen `begriff`, `thema` oder `titel` sowie gängige Inhalts-Spaltennamen.

## Anpassung 24.08.2026 – Verben

Die Verbenverwaltung verwendet nun die Tabellenfelder `verb_it` und `verb_de`.
Die Suche durchsucht Italienisch, Deutsch, Präsens, Perfekt, Futur, Imperativ und Endung und unterstützt Teilwörter.
Der PDF-Ausdruck kann wahlweise nach Italienisch A–Z oder Deutsch A–Z sortiert werden.
Die Datei `database/migration_verben_aktuelle_struktur.sql` enthält die ergänzende Datenbankmigration für AUTO_INCREMENT und den Volltextindex.
