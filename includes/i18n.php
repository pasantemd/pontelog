<?php
declare(strict_types=1);

const PONTELOG_LANGS = ['es', 'en', 'pt', 'zh'];

/**
 * Orden de detección:
 * 1) ?lang=xx (elección manual, se guarda en cookie)
 * 2) cookie pontelog_lang
 * 3) cabecera Accept-Language del navegador (región/idioma preferido)
 * 4) español por defecto
 */
function pontelog_detect_lang(): string
{
    if (isset($_GET['lang'])) {
        $requested = strtolower(trim((string) $_GET['lang']));
        if (in_array($requested, PONTELOG_LANGS, true)) {
            pontelog_remember_lang($requested);
            return $requested;
        }
    }

    $cookie = strtolower(trim((string) ($_COOKIE['pontelog_lang'] ?? '')));
    if (in_array($cookie, PONTELOG_LANGS, true)) {
        return $cookie;
    }

    return pontelog_lang_from_accept($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') ?: 'es';
}

function pontelog_remember_lang(string $lang): void
{
    if (!in_array($lang, PONTELOG_LANGS, true)) {
        return;
    }
    setcookie('pontelog_lang', $lang, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['pontelog_lang'] = $lang;
}

function pontelog_lang_from_accept(string $header): ?string
{
    if ($header === '') {
        return null;
    }

    $parts = [];
    foreach (explode(',', $header) as $piece) {
        $piece = trim($piece);
        if ($piece === '') {
            continue;
        }
        [$tag, $q] = array_pad(explode(';', $piece), 2, 'q=1');
        $tag = strtolower(trim($tag));
        $quality = 1.0;
        if (preg_match('/q=([0-9.]+)/', (string) $q, $match)) {
            $quality = (float) $match[1];
        }
        $parts[] = ['tag' => $tag, 'q' => $quality];
    }

    usort($parts, static fn(array $a, array $b): int => $b['q'] <=> $a['q']);

    foreach ($parts as $part) {
        $tag = $part['tag'];
        $primary = explode('-', $tag)[0];
        if ($primary === 'zh' || str_starts_with($tag, 'zh')) {
            return 'zh';
        }
        if (in_array($primary, PONTELOG_LANGS, true)) {
            return $primary;
        }
    }

    return null;
}

function pontelog_load_translations(string $lang): array
{
    static $cache = [];
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }
    $path = dirname(__DIR__) . '/lang/' . $lang . '.php';
    $fallback = dirname(__DIR__) . '/lang/es.php';
    $messages = is_file($path) ? require $path : [];
    $base = is_file($fallback) ? require $fallback : [];
    if (!is_array($messages)) {
        $messages = [];
    }
    if (!is_array($base)) {
        $base = [];
    }
    $cache[$lang] = $messages + $base;
    return $cache[$lang];
}

function t(string $key, array $replace = []): string
{
    global $lang;
    $messages = pontelog_load_translations(is_string($lang ?? null) ? $lang : 'es');
    $text = $messages[$key] ?? $key;
    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }
    return $text;
}

function lang_url(string $code): string
{
    global $base, $page;
    $code = strtolower($code);
    if (!in_array($code, PONTELOG_LANGS, true)) {
        $code = 'es';
    }
    $current = is_string($page ?? null) ? $page : 'inicio';
    if ($current === 'inicio') {
        return $base . '?lang=' . rawurlencode($code);
    }
    return $base . rawurlencode($current) . '/?lang=' . rawurlencode($code);
}

function pontelog_html_lang(string $lang): string
{
    return match ($lang) {
        'pt' => 'pt',
        'zh' => 'zh-Hans',
        'en' => 'en',
        default => 'es',
    };
}

function pontelog_lang_label(string $code): string
{
    return match ($code) {
        'en' => 'EN',
        'pt' => 'PT',
        'zh' => '中文',
        default => 'ES',
    };
}
