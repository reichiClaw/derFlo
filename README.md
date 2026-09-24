# derFlo – Private Cooking mit Florian Karrer

Statische Website für das Private-Cooking-Angebot von Florian Karrer (Marke „derFlo“, Lech am Arlberg).
Deutsch und Englisch, ohne Framework, ohne Build-Prozess, ohne externe Dienste.

## Inhalt

- [Gestalterische Richtung](#gestalterische-richtung)
- [Dateien](#dateien)
- [Lokal öffnen](#lokal-öffnen)
- [Hochladen](#hochladen)
- [Anfragehilfe (PHP-Mailversand)](#anfragehilfe-php-mailversand)
- [Fotos einsetzen](#fotos-einsetzen)
- [Vor Veröffentlichung noch benötigt](#vor-veröffentlichung-noch-benötigt)
- [Was geprüft wurde](#was-geprüft-wurde)
- [Mögliche spätere Erweiterungen](#mögliche-spätere-erweiterungen)
- [Lizenzen](#lizenzen)

## Gestalterische Richtung

**Kulinarisches Magazin trifft persönliche Website eines Kochs.**

- **Farben:** warmes Elfenbein (`#f4efe6`) für helle Flächen, tiefes Anthrazit (`#232122`) für Text und den Abschnitt „Über Flo“, dunkles Weinrot (`#6d1f2f`) als einzige Akzentfarbe – für die kursive Hälfte der Headline, Nummern, Schaltflächen und feine Linien. Kein Farbwechsel pro Abschnitt.
- **Schrift:** *Fraunces* (weiche, charaktervolle Serif mit optischen Größen) für Headlines, Zahlen und Zitate; *Inter* für Fließtext und Bedienelemente. Beide liegen lokal als variable WOFF2 (Latin-Subset) in `assets/fonts/`, es wird nichts extern geladen.
- **Layout:** mobile first, darauf aufbauend asymmetrische 12-Spalten-Raster auf großen Bildschirmen. Formate als nummerierte redaktionelle Liste mit Hairlines statt Kartenraster; Prozess als drei Spalten mit großen Ziffern; Einblicke als Mosaik mit unterschiedlichen Seitenverhältnissen; Kontakt zweispaltig mit klebender Einleitung.
- **Bildmarke:** das „F“ mit Punkt von karrer.kitchen (`assets/img/logo.svg`) steht in Kopfzeile, Footer, Favicon und OG-Bild und ersetzt im Abschnitt „Über Flo“ den Zitatbalken. Das Original ist Orange (`#dc4114`); auf der Website läuft es in der Akzentfarbe Weinrot bzw. Elfenbein auf dunklem Grund, damit die Seite bei einer Akzentfarbe bleibt. Soll das Orange erhalten bleiben, in `logo.svg` und `favicon.svg` den `fill` ändern und `.pull__mark` in `styles.css` eine feste Farbe geben.
- **Ton:** Ich-Perspektive, „du“, herzlich und leicht augenzwinkernd. Leitidee „Du lädst ein. Ich koche.“, der bestehende Claim „Cooking is not a crime“ als sekundäres Band und Zitat.
- **Bewegung:** nur dezente Hover-Übergänge und eine kleine Einblendung beim Scrollen. Mit `prefers-reduced-motion` oder ohne JavaScript ist alles sofort und vollständig sichtbar.

## Dateien

```
index.html          Deutsche Startseite (alle Abschnitte, Anfragehilfe)
en.html             Vollständig übersetzte englische Version
styles.css          Gesamtes Stylesheet (Design-Token als CSS Custom Properties)
script.js           Optionales Vanilla-JS: mobile Navigation, Einblendungen, Formularprüfung, Senden ohne Seitenwechsel
anfrage.php         Formular-Endpunkt: Prüfung, Mailversand per mail(), Fehler-/Erfolgsseiten (DE/EN) und JSON-Antwort
impressum.html      Impressum – ENTWURF mit markierten Pflichtangaben
datenschutz.html    Datenschutzerklärung – ENTWURF, beschreibt exakt die technische Realität der Website
assets/fonts/       Fraunces und Inter (WOFF2) inkl. Lizenzen
assets/img/         logo.svg (Bildmarke), favicon.svg, og-image.png (DE), og-image-en.png (EN)
```

Alle Pfade sind relativ; die Website funktioniert auch in einem Unterverzeichnis (z. B. `https://domain.tld/privatecooking/`).

## Lokal öffnen

**Ohne PHP** (alles außer dem Formularversand):
`index.html` per Doppelklick im Browser öffnen. Navigation, Sprachwechsel, Kontaktlinks und alle Inhalte funktionieren. Das Formular führt beim Absenden zu einem Download bzw. Fehler, weil kein PHP läuft – das ist erwartbar.

**Mit PHP** (vollständig), im Projektordner:

```bash
php -S localhost:8080
```

Dann `http://localhost:8080/` öffnen. Der Mailversand funktioniert lokal nur, wenn ein Mailprogramm konfiguriert ist; ohne Mailserver zeigt die Seite ehrlich „Das hat gerade nicht geklappt“ mit dem Direktkontakt.

## Hochladen

1. Den gesamten Inhalt dieses Ordners (inkl. `assets/`) per FTP/SFTP in das Web-Verzeichnis des Hosters laden (z. B. `httpdocs/`, `public_html/` oder `html/`).
2. Voraussetzung ist gewöhnlicher Webspace mit **PHP 7.4 oder neuer** und aktivierter `mail()`-Funktion (Standard bei fast allen österreichischen und deutschen Hostern).
3. In `anfrage.php` oben die Konfiguration prüfen (siehe nächster Abschnitt).
4. Einmal eine echte Testanfrage abschicken und prüfen, ob die Mail bei `florian@karrer.kitchen` ankommt (auch im Spam-Ordner nachsehen).

Es gibt keinen Build-Schritt, keine Datenbank und keine Abhängigkeiten.

## Anfragehilfe (PHP-Mailversand)

`anfrage.php` nimmt die Formulare aus `index.html` und `en.html` entgegen.

**Konfiguration** (Anfang der Datei):

| Konstante | Bedeutung |
|---|---|
| `MAIL_TO` | Empfänger der Anfragen, aktuell `florian@karrer.kitchen` |
| `MAIL_FROM` | Absenderadresse der Website-Mails, aktuell `anfrage@karrer.kitchen`. **Muss mit dem Hoster abgestimmt werden:** Viele Hoster verschicken nur mit Absendern der eigenen Domain. Die Adresse muss nicht als Postfach existieren, sollte aber zur Domain des Webspace gehören. |
| `MAIL_FROM_NAME` | Anzeigename des Absenders |

Die E-Mail-Adresse des Gasts steht im `Reply-To`, sodass Florian direkt auf die Anfrage antworten kann.

**Verhalten:**

- Pflichtfelder: Name, E-Mail, Nachricht (mindestens 10 Zeichen). Alles andere ist optional.
- Ohne JavaScript: Bei Fehlern zeigt `anfrage.php` das Formular mit allen bisherigen Eingaben und klar zugeordneten Hinweisen erneut an; bei Erfolg eine Bestätigungsseite; bei fehlgeschlagenem Versand eine ehrliche Fehlermeldung mit Direktkontakt.
- Mit JavaScript: Eingaben werden vorab geprüft, der Versand läuft ohne Seitenwechsel, das Ergebnis erscheint direkt im Formular (Live-Region).
- Spam-Schutz: unsichtbares Honeypot-Feld. Eingaben werden von Zeilenumbrüchen befreit, die E-Mail wird validiert (schützt vor Header-Injection).
- Es werden **keine Daten gespeichert** – weder auf dem Server noch im Browser – und keine Drittanbieter angesprochen.

## Fotos einsetzen

Es liegen aktuell keine freigegebenen Originalfotos vor. Die Website nutzt deshalb gestaltete Ersatzflächen (`<div class="ph …">`), die Format und Platz bereits reservieren, damit nichts springt.

Sobald Fotos da sind, jede Fläche durch ein Bild ersetzen – Beispiel Hero:

```html
<!-- vorher -->
<div class="ph ph--wine" aria-hidden="true"><span class="ph__label">Beim Anrichten</span></div>

<!-- nachher -->
<img src="assets/img/flo-anrichten.jpg" width="1200" height="1500"
     alt="Florian Karrer richtet einen Teller an" fetchpriority="high">
```

Für alle Bilder unterhalb des ersten Bildschirms zusätzlich `loading="lazy"` und `decoding="async"` setzen. `width`/`height` immer in den tatsächlichen Pixelmaßen angeben (reserviert den Platz). Empfohlene Formate: JPG oder WebP, Hero ca. 1200 × 1500 px (4:5), Porträt ca. 900 × 1200 px (3:4), Galerie je nach Fläche 900–1600 px Breite, Qualität ~80 %. Dieselben Bilder in `en.html` einsetzen (mit englischem `alt`).

Auch das OG-Bild (`assets/img/og-image.png`, 1200 × 630 px) kann später durch eine Version mit echtem Foto ersetzt werden.

## Vor Veröffentlichung noch benötigt

**Inhalte und Freigaben**

- [ ] Hero-Foto: Flo beim Anrichten oder in einer echten Kochsituation, Hochformat 4:5, mit Nutzungsfreigabe des Fotografen.
- [ ] Porträt von Florian für „Über Flo“, Hochformat 3:4, freigegeben.
- [ ] 4–6 Bilder für „Einblicke“: Gericht (3:2), Hände beim Anrichten (4:5), Zutaten (4:5), Tischsituation (16:9), am Herd (1:1), Detail (1:1). Nur eigene Gerichte, nur freigegebenes Material.
- [ ] Bestätigung, dass die Bildmarke (F mit Punkt) in Weinrot statt im Original-Orange verwendet werden darf; das Oktopuslogo von karrer.kitchen liegt weiterhin nicht als Datei vor und wurde nicht nachgebaut.
- [ ] Bestätigung der Texte durch Florian, insbesondere die Zahlen in „Über Flo“ (15 Jahre Küche, 19 Saisonen, Schlegelkopf, Achterdeck by Aichinger, Vila Joya) und die Zusage „in der Regel innerhalb von 24 Stunden“ in der Anfrage-Bestätigung.
- [ ] Entscheidung, ob Social-Media-Profile verlinkt werden sollen (derzeit keine bekannt, daher keine Links).

**Domain und Technik**

- [ ] Bestätigte Hauptdomain (z. B. derflo.at oder karrer.kitchen). Danach in `index.html` und `en.html`: `<link rel="canonical">` ergänzen, `hreflang`-Links und `og:image`/`og:url` auf absolute URLs setzen (die Stellen sind im `<head>` kommentiert).
- [ ] Hoster und PHP-Version bestätigen; `MAIL_FROM` in `anfrage.php` mit dem Hoster abstimmen; Testanfrage durchführen.
- [ ] Optional: SPF-Eintrag der Domain um den Mailserver des Hosters ergänzen, damit Formularmails nicht im Spam landen.

**Rechtliches** (Entwürfe in `impressum.html` und `datenschutz.html`; markierte Stellen `[…]`)

- [ ] Geschäftsanschrift (Straße, PLZ, Ort).
- [ ] Rechtsform, Unternehmensgegenstand laut Gewerbeberechtigung, zuständige Gewerbebehörde, Kammerzugehörigkeit, ggf. UID-Nummer.
- [ ] Name und Anschrift des Hosting-Anbieters sowie dessen Speicherdauer für Logfiles; Auftragsverarbeitungsvertrag prüfen.
- [ ] Konkrete Löschfrist für Anfragen festlegen.
- [ ] Veröffentlichungsdatum der Datenschutzerklärung eintragen.
- [ ] Fotografen-Credits, sobald Fotos eingesetzt sind.
- [ ] Prüfung beider Texte durch eine fachkundige Stelle. Die Entwürfe beschreiben die technische Realität der Website korrekt, ersetzen aber keine Rechtsberatung und behaupten keine vollständige DSGVO-Konformität.

Die englische Version verlinkt auf die deutschen Rechtstexte (in Österreich üblich). Eine englische Übersetzung von Impressum und Datenschutz kann bei Bedarf ergänzt werden.

## Was geprüft wurde

Durchgeführt in dieser Umgebung (Linux, Chrome 148 headless, PHP 8.3 CLI):

- HTML aller vier Seiten sowie der PHP-Fehlerseite mit dem W3C Nu Validator geprüft: keine Fehler und keine Warnungen.
- `php -l anfrage.php`: keine Syntaxfehler.
- Formular-Endpunkt mit `curl` getestet: Validierungsfehler (JSON und HTML mit erhaltenen Feldwerten), Erfolg (Mailinhalt über einen lokalen Test-Sendmail geprüft), fehlgeschlagener Versand, Honeypot, Header-Injection-Versuch über die E-Mail-Adresse, GET-Aufruf (Weiterleitung zur Startseite).
- Screenshots der deutschen Seite bei 320, 375, 768 und 1440 px Breite (alle Abschnitte): keine horizontalen Scrollbalken, keine abgeschnittenen Überschriften, keine überlappenden Elemente.
- Interaktive Prüfung im Browser: mobile Navigation (öffnen, schließen, Escape, Tastatur), Sprachwechsel DE↔EN, Sprunglinks, Kontaktlinks, Formularprüfung mit und ohne JavaScript.
- Alle internen Links und Anker automatisiert auf Existenz geprüft; keine doppelten IDs.

Nicht geprüft (keine Geräte verfügbar): Safari/iOS, Firefox, echte Touch-Geräte, Screenreader-Ausgabe, Ladezeiten über ein Mobilfunknetz. Diese Prüfungen sollten nach dem Upload auf dem Zielserver nachgeholt werden.

## Mögliche spätere Erweiterungen

- **Externer Formular-Endpunkt** (z. B. ein Formular-Dienst oder ein eigener Mail-API-Anbieter) statt `mail()`, falls der Hoster keinen zuverlässigen Mailversand bietet. Dafür nur das `action`-Attribut der Formulare und die Konfiguration in `anfrage.php` bzw. `script.js` anpassen. Für die gelieferte Version ist das nicht erforderlich.
- Englische Fassung von Impressum und Datenschutz.
- Ein zweites OG-Bild mit echtem Foto.
- Weitere Sprache (z. B. Französisch) nach dem Muster von `en.html`.
- AGB-Seite (`agb.html`) mit Leistungsumfang und Arbeitsweise, verlinkt im Footer neben Impressum und Datenschutz – sobald der AGB-Text vorliegt.

## Lizenzen

- Texte, Gestaltung und Code: für Florian Karrer erstellt.
- Fraunces © The Fraunces Project Authors (Undercase Type) – SIL Open Font License 1.1, siehe `assets/fonts/LICENSE-Fraunces-OFL.txt`.
- Inter © The Inter Project Authors (Rasmus Andersson) – SIL Open Font License 1.1, siehe `assets/fonts/LICENSE-Inter-OFL.txt`.
- Die Schriftdateien sind auf den lateinischen Zeichenvorrat reduziert (erlaubt unter OFL, die Dateinamen enthalten den Zusatz „Latin“).

## ZIP für die Übergabe erzeugen

Im Projektordner (ohne Git-Dateien):

```bash
zip -r derflo-website.zip index.html en.html styles.css script.js anfrage.php impressum.html datenschutz.html README.md assets
```
