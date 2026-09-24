<?php
/**
 * derFlo – Anfragehilfe
 *
 * Nimmt das Formular von index.html / en.html entgegen, prüft die Eingaben
 * und schickt sie per E-Mail an Florian. Funktioniert mit und ohne JavaScript:
 *
 *  - Ohne JavaScript: Klassischer POST. Bei Fehlern wird das Formular mit den
 *    bereits ausgefüllten Werten und verständlichen Hinweisen erneut angezeigt,
 *    bei Erfolg eine kurze Bestätigungsseite.
 *  - Mit JavaScript (Accept: application/json): Antwort als JSON, die Seite
 *    zeigt das Ergebnis direkt im Formular an.
 *
 * Es werden keine Daten gespeichert. Der Versand läuft ausschließlich über die
 * PHP-Funktion mail() des eigenen Webspace. Kein Drittanbieter.
 *
 * Voraussetzungen auf dem Webspace: PHP 7.4 oder neuer, mail() aktiv.
 * Viele Hoster verlangen, dass die Absenderadresse (MAIL_FROM) zu einer Domain
 * gehört, die auf demselben Webspace liegt. Deshalb ist die Absenderadresse
 * unten separat einstellbar – die E-Mail des Gasts landet im Reply-To.
 */

declare(strict_types=1);

// --------------------------------------------------------------------------
// Konfiguration – vor dem Hochladen prüfen
// --------------------------------------------------------------------------
const MAIL_TO      = 'florian@karrer.kitchen';           // Empfänger der Anfragen
const MAIL_FROM    = 'anfrage@karrer.kitchen';           // Absender; muss meist zur Hosting-Domain passen (TODO: mit Hoster abstimmen)
const MAIL_FROM_NAME = 'derFlo Website';
const SITE_NAME    = 'derFlo – Florian Karrer';
const MAX_LENGTH   = 5000;                                // Zeichenlimit für die Nachricht

// --------------------------------------------------------------------------
// Sprache
// --------------------------------------------------------------------------
$lang = (isset($_POST['lang']) && $_POST['lang'] === 'en') ? 'en' : 'de';

$t = [
    'de' => [
        'title'          => 'Deine Anfrage',
        'back'           => 'Zurück zur Startseite',
        'back_href'      => 'index.html#anfrage',
        'heading_error'  => 'Da fehlt noch etwas.',
        'intro_error'    => 'Bitte prüfe die markierten Felder. Deine bisherigen Angaben sind noch da.',
        'heading_ok'     => 'Vielen Dank für deine Anfrage!',
        'intro_ok'       => 'Auf Basis deiner Angaben stelle ich ein individuelles Menü und das zugehörige Angebot zusammen – das bekommst du in der Regel innerhalb von 24 Stunden per Mail. Wenn es eilt, ruf mich einfach an.',
        'heading_fail'   => 'Das hat gerade nicht geklappt.',
        'intro_fail'     => 'Die Anfrage konnte nicht versendet werden. Bitte schreib mir direkt oder ruf an – ich freue mich darauf.',
        'method'         => 'Diese Seite nimmt nur ausgefüllte Anfragen entgegen.',
        'name'           => 'Dein Name',
        'email'          => 'Deine E-Mail-Adresse',
        'phone'          => 'Telefon',
        'date'           => 'Datum oder Zeitraum',
        'location'       => 'Ort',
        'guests'         => 'Ungefähre Gästezahl',
        'format'         => 'Gewünschtes Format',
        'format_open'    => 'Noch offen – berate mich gern',
        'message'        => 'Deine Nachricht',
        'optional'       => '(optional)',
        'submit'         => 'Anfrage senden',
        'privacy'        => 'Unverbindlich. Deine Angaben verwende ich ausschließlich, um deine Anfrage zu beantworten.',
        'err_required'   => 'Bitte fülle dieses Feld aus.',
        'err_email'      => 'Bitte gib eine gültige E-Mail-Adresse ein.',
        'err_short'      => 'Ein paar Sätze mehr helfen mir, deine Anfrage einzuordnen.',
        'err_long'       => 'Die Nachricht ist etwas zu lang. Bitte kürze sie ein wenig.',
        'err_fix'        => 'Bitte prüfe die markierten Felder.',
        'ok_message'     => 'Vielen Dank für deine Anfrage! Auf Basis deiner Angaben stelle ich ein individuelles Menü und das zugehörige Angebot zusammen – das bekommst du in der Regel innerhalb von 24 Stunden per Mail. Mit kulinarischem Gruß, Flo',
        'ok_status'      => 'Deine Anfrage ist bei mir angekommen. Mit kulinarischem Gruß, Flo',
        'fail_message'   => 'Das Senden hat gerade nicht funktioniert. Schreib mir bitte direkt an florian@karrer.kitchen oder ruf an: +43 660 14 11 020.',
        'mail_subject'   => 'Neue Anfrage über derFlo',
        'mail_intro'     => 'Neue Anfrage über die Website (Sprache: Deutsch)',
        'not_given'      => 'keine Angabe',
        'direct'         => 'Direkt erreichst du mich hier:',
    ],
    'en' => [
        'title'          => 'Your enquiry',
        'back'           => 'Back to the homepage',
        'back_href'      => 'en.html#enquiry',
        'heading_error'  => 'Something’s still missing.',
        'intro_error'    => 'Please check the highlighted fields. Everything you’ve entered so far is still here.',
        'heading_ok'     => 'Thank you for your enquiry!',
        'intro_ok'       => 'Based on your details, I’ll put together an individual menu and the matching offer – you’ll usually receive it by email within 24 hours. If it’s urgent, just give me a call.',
        'heading_fail'   => 'That didn’t work just now.',
        'intro_fail'     => 'Your enquiry could not be sent. Please email or call me directly – I’m looking forward to hearing from you.',
        'method'         => 'This page only accepts submitted enquiries.',
        'name'           => 'Your name',
        'email'          => 'Your email address',
        'phone'          => 'Phone',
        'date'           => 'Date or period',
        'location'       => 'Location',
        'guests'         => 'Approximate number of guests',
        'format'         => 'Preferred format',
        'format_open'    => 'Still open – happy to advise',
        'message'        => 'Your message',
        'optional'       => '(optional)',
        'submit'         => 'Send enquiry',
        'privacy'        => 'No obligation. I only use your details to answer your enquiry.',
        'err_required'   => 'Please fill in this field.',
        'err_email'      => 'Please enter a valid email address.',
        'err_short'      => 'A few more sentences help me understand what you have in mind.',
        'err_long'       => 'Your message is a little too long. Please shorten it a bit.',
        'err_fix'        => 'Please check the highlighted fields.',
        'ok_message'     => 'Thank you for your enquiry! Based on your details, I’ll put together an individual menu and the matching offer – you’ll usually receive it by email within 24 hours. With culinary regards, Flo',
        'ok_status'      => 'Your enquiry has reached me. With culinary regards, Flo',
        'fail_message'   => 'Sending didn’t work just now. Please email me directly at florian@karrer.kitchen or call +43 660 14 11 020.',
        'mail_subject'   => 'New enquiry via derFlo',
        'mail_intro'     => 'New enquiry via the website (language: English)',
        'not_given'      => 'not given',
        'direct'         => 'You can reach me directly here:',
    ],
][$lang];

// Auswahlwerte des Formulars (DE und EN); nur diese werden übernommen.
$formats = ['Fine Dining', 'Shared Table', 'Flying Buffet', 'Traditionsküche', 'Traditional cuisine'];
$formatOptions = ['Fine Dining', 'Shared Table', 'Flying Buffet', $lang === 'en' ? 'Traditional cuisine' : 'Traditionsküche'];

$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

// --------------------------------------------------------------------------
// Hilfsfunktionen
// --------------------------------------------------------------------------
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field(string $key, int $max = 300): string
{
    $value = isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function singleLine(string $value): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', $value) ?? '');
}

function respondJson(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// --------------------------------------------------------------------------
// Nur POST verarbeiten; direkte Aufrufe zurück zur Startseite
// --------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $t['back_href'], true, 303);
    exit;
}

// --------------------------------------------------------------------------
// Eingaben lesen und prüfen
// --------------------------------------------------------------------------
$data = [
    'name'     => singleLine(field('name', 120)),
    'email'    => singleLine(field('email', 200)),
    'phone'    => singleLine(field('phone', 60)),
    'date'     => singleLine(field('date', 120)),
    'location' => singleLine(field('location', 200)),
    'guests'   => singleLine(field('guests', 40)),
    'format'   => singleLine(field('format', 60)),
    'message'  => field('message', MAX_LENGTH + 500),
];
$honeypot = field('website', 200);

$errors = [];

if ($data['name'] === '') {
    $errors['name'] = $t['err_required'];
}
if ($data['email'] === '') {
    $errors['email'] = $t['err_required'];
} elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = $t['err_email'];
}
if ($data['message'] === '') {
    $errors['message'] = $t['err_required'];
} elseif ((function_exists('mb_strlen') ? mb_strlen($data['message'], 'UTF-8') : strlen($data['message'])) < 10) {
    $errors['message'] = $t['err_short'];
} elseif ((function_exists('mb_strlen') ? mb_strlen($data['message'], 'UTF-8') : strlen($data['message'])) > MAX_LENGTH) {
    $errors['message'] = $t['err_long'];
}
if ($data['format'] !== '' && !in_array($data['format'], $formats, true)) {
    $data['format'] = '';
}

// --------------------------------------------------------------------------
// Versand
// --------------------------------------------------------------------------
$sent = false;

if (!$errors) {
    if ($honeypot !== '') {
        // Ausgefülltes Honeypot-Feld: sehr wahrscheinlich ein Bot. Still „erfolgreich“ beantworten.
        $sent = true;
    } else {
        // Die Mail liest Florian – deshalb neutrale deutsche Beschriftungen, unabhängig von der Seitensprache.
        $na = 'keine Angabe';
        $lines = [
            $t['mail_intro'],
            str_repeat('-', 48),
            'Name:            ' . $data['name'],
            'E-Mail:          ' . $data['email'],
            'Telefon:         ' . ($data['phone'] !== '' ? $data['phone'] : $na),
            'Datum/Zeitraum:  ' . ($data['date'] !== '' ? $data['date'] : $na),
            'Ort:             ' . ($data['location'] !== '' ? $data['location'] : $na),
            'Gästezahl:       ' . ($data['guests'] !== '' ? $data['guests'] : $na),
            'Format:          ' . ($data['format'] !== '' ? $data['format'] : $na),
            str_repeat('-', 48),
            'Nachricht:',
            '',
            $data['message'],
            '',
            str_repeat('-', 48),
            'Gesendet: ' . date('d.m.Y H:i') . ' (Serverzeit)',
        ];
        $body = implode("\n", $lines);

        $subject = $t['mail_subject'] . ': ' . $data['name'];
        if (function_exists('mb_encode_mimeheader')) {
            $subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
        }

        $fromName = MAIL_FROM_NAME;
        if (function_exists('mb_encode_mimeheader')) {
            $fromName = mb_encode_mimeheader($fromName, 'UTF-8', 'B', "\r\n");
        }

        $headers = [
            'From: ' . $fromName . ' <' . MAIL_FROM . '>',
            'Reply-To: ' . $data['email'],
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: derFlo-Website',
        ];

        $sent = @mail(MAIL_TO, $subject, $body, implode("\r\n", $headers), '-f' . MAIL_FROM);
        if (!$sent) {
            // Zweiter Versuch ohne Envelope-Sender (manche Hoster verbieten den Parameter).
            $sent = @mail(MAIL_TO, $subject, $body, implode("\r\n", $headers));
        }
    }
}

// --------------------------------------------------------------------------
// Antwort für JavaScript
// --------------------------------------------------------------------------
if ($wantsJson) {
    if ($errors) {
        respondJson(422, ['ok' => false, 'message' => $t['err_fix'], 'errors' => $errors]);
    }
    if ($sent) {
        respondJson(200, ['ok' => true, 'message' => $t['ok_message']]);
    }
    respondJson(500, ['ok' => false, 'message' => $t['fail_message']]);
}

// --------------------------------------------------------------------------
// Antwort ohne JavaScript: vollständige Seite
// --------------------------------------------------------------------------
http_response_code($errors ? 422 : ($sent ? 200 : 500));
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$state = $errors ? 'error' : ($sent ? 'ok' : 'fail');
$heading = $t['heading_' . $state];
$intro = $t['intro_' . $state];

function renderField(array $t, array $data, array $errors, string $key, string $type = 'text', bool $required = false): string
{
    $label = e($t[$key]) . ($required ? '' : ' <span class="optional">' . e($t['optional']) . '</span>');
    $invalid = isset($errors[$key]);
    $html  = '<div class="field' . ($invalid ? ' is-invalid' : '') . '">';
    $html .= '<label for="' . $key . '">' . $label . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea id="' . $key . '" name="' . $key . '" rows="6"' . ($required ? ' required' : '') . ($invalid ? ' aria-invalid="true"' : '') . ' aria-describedby="' . $key . '-error">' . e($data[$key]) . '</textarea>';
    } else {
        $html .= '<input type="' . $type . '" id="' . $key . '" name="' . $key . '" value="' . e($data[$key]) . '"' . ($required ? ' required' : '') . ($invalid ? ' aria-invalid="true"' : '') . ' aria-describedby="' . $key . '-error">';
    }
    $html .= '<p class="field__error" id="' . $key . '-error">' . ($invalid ? e($errors[$key]) : '') . '</p>';
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($t['title']) ?> – <?= e(SITE_NAME) ?></title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a class="brand" href="<?= $lang === 'en' ? 'en.html' : 'index.html' ?>">
        <img class="brand__mark" src="assets/img/logo.svg" width="134" height="580" alt="">
        <span class="brand__text">
          <span class="brand__name">derFlo</span>
          <span class="brand__sub">Florian Karrer · Private Cooking</span>
        </span>
      </a>
    </div>
  </header>

  <main class="page" id="main">
    <div class="wrap">
      <div class="page__inner">
        <a class="back" href="<?= e($t['back_href']) ?>">← <?= e($t['back']) ?></a>
        <p class="eyebrow"><?= e($t['title']) ?></p>
        <h1><?= e($heading) ?></h1>
        <p class="lead"><?= e($intro) ?></p>

        <?php if ($state === 'error'): ?>
          <form class="form" action="anfrage.php" method="post" novalidate style="margin-top: 2.5rem;">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <div class="honeypot" aria-hidden="true">
              <label for="website">Bitte dieses Feld leer lassen</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form__status is-error" role="alert"><?= e($t['err_fix']) ?></div>

            <div class="form__row form__row--2">
              <?= renderField($t, $data, $errors, 'name', 'text', true) ?>
              <?= renderField($t, $data, $errors, 'email', 'email', true) ?>
            </div>
            <div class="form__row form__row--2">
              <?= renderField($t, $data, $errors, 'phone', 'tel') ?>
              <?= renderField($t, $data, $errors, 'date') ?>
            </div>
            <div class="form__row form__row--2">
              <?= renderField($t, $data, $errors, 'location') ?>
              <?= renderField($t, $data, $errors, 'guests') ?>
            </div>

            <div class="field">
              <label for="format"><?= e($t['format']) ?> <span class="optional"><?= e($t['optional']) ?></span></label>
              <select id="format" name="format">
                <option value=""><?= e($t['format_open']) ?></option>
                <?php foreach ($formatOptions as $f): ?>
                  <option value="<?= e($f) ?>"<?= $data['format'] === $f ? ' selected' : '' ?>><?= e($f) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <?= renderField($t, $data, $errors, 'message', 'textarea', true) ?>

            <div class="form__footer">
              <button class="btn btn--primary" type="submit"><?= e($t['submit']) ?> <span class="arrow" aria-hidden="true">→</span></button>
              <p class="form__privacy"><?= e($t['privacy']) ?></p>
            </div>
          </form>

        <?php else: ?>
          <div class="form__status <?= $state === 'ok' ? 'is-success' : 'is-error' ?>" role="status" style="margin-top: 2rem;">
            <?= e($state === 'ok' ? $t['ok_status'] : $t['fail_message']) ?>
          </div>

          <h2><?= e($t['direct']) ?></h2>
          <address class="contact__direct">
            <div>
              <small>E-Mail</small>
              <a href="mailto:florian@karrer.kitchen">florian@karrer.kitchen</a>
            </div>
            <div>
              <small><?= $lang === 'en' ? 'Phone' : 'Telefon' ?></small>
              <a href="tel:+436601411020">+43 660 14 11 020</a>
            </div>
          </address>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <div class="wrap">
      <div class="site-footer__bottom" style="margin-top:0; border-top:0; padding-top:0;">
        <span>© <?= date('Y') ?> Florian Karrer</span>
        <span><a href="impressum.html">Impressum</a> · <a href="datenschutz.html"><?= $lang === 'en' ? 'Privacy' : 'Datenschutz' ?></a></span>
      </div>
    </div>
  </footer>
</body>
</html>
