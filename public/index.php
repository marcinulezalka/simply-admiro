<?php

declare(strict_types=1);

// Placeholder wygenerowany przez Smartboard dla "simplysmart".
// Zastąp ten plik (albo cały katalog public/) swoją aplikacją.

const VERSION_FILE = __DIR__ . '/../VERSION';
const LOGO_FILE = __DIR__ . '/assets/img/brand/logo.svg';
// Kolejność = kolejność kaskady: tokeny, baza, komponenty.
const STYLESHEETS = ['tokens.css', 'base.css', 'app.css'];

// Wersja z pliku VERSION jako parametr ?v= — po wydaniu przeglądarki pobiorą nowe assety.
$assetVersion = rawurlencode(is_readable(VERSION_FILE) ? trim((string) file_get_contents(VERSION_FILE)) : '');
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>simplysmart</title>
<?php foreach (STYLESHEETS as $stylesheet): ?>
    <link rel="stylesheet" href="/assets/css/<?= $stylesheet ?>?v=<?= $assetVersion ?>">
<?php endforeach; ?>
</head>
<body>
    <main class="ss-page">
        <h1 class="ss-logo"><?php readfile(LOGO_FILE); ?></h1>
        <p>Projekt utworzony przez Smartboard. Domena: <code>mainboard.local</code>.</p>
        <p class="ss-muted">Zastąp ten plik swoją aplikacją w <code>public/</code>.</p>
    </main>
</body>
</html>
