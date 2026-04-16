<?php

function blocklist_supported_languages() {
    return ['english', 'telugu', 'hindi', 'gujarati', 'malayalam'];
}

function blocklist_config_directory() {
    return dirname(__DIR__) . '/config/blocklists';
}

function blocklist_file_path($type, $language) {
    $language = strtolower(trim((string)$language));
    if (!in_array($language, blocklist_supported_languages(), true)) {
        return null;
    }

    $type = strtolower(trim((string)$type));
    if ($type === 'moderation') {
        return blocklist_config_directory() . '/moderation_' . $language . '.txt';
    }

    if ($type === 'themes') {
        return blocklist_config_directory() . '/themes_' . $language . '.txt';
    }

    return null;
}

function blocklist_load_entries($type, $language) {
    static $cache = [];

    $language = strtolower(trim((string)$language));
    $type = strtolower(trim((string)$type));
    $cacheKey = $type . '|' . $language;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $filePath = blocklist_file_path($type, $language);
    if ($filePath === null) {
        $result = [
            'ok' => false,
            'entries' => [],
            'error' => 'Unsupported blocklist type or language: ' . $type . '/' . $language,
            'file' => null,
        ];
        $cache[$cacheKey] = $result;
        return $result;
    }

    if (!is_file($filePath)) {
        $result = [
            'ok' => false,
            'entries' => [],
            'error' => 'Missing blocklist file: ' . $filePath,
            'file' => $filePath,
        ];
        $cache[$cacheKey] = $result;
        return $result;
    }

    if (!is_readable($filePath)) {
        $result = [
            'ok' => false,
            'entries' => [],
            'error' => 'Unreadable blocklist file: ' . $filePath,
            'file' => $filePath,
        ];
        $cache[$cacheKey] = $result;
        return $result;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        $result = [
            'ok' => false,
            'entries' => [],
            'error' => 'Failed to read blocklist file: ' . $filePath,
            'file' => $filePath,
        ];
        $cache[$cacheKey] = $result;
        return $result;
    }

    $entries = [];
    foreach ($lines as $line) {
        $value = trim((string)$line);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        if ($value === '' || strpos($value, '#') === 0) {
            continue;
        }
        $entries[] = $value;
    }

    $entries = array_values(array_unique($entries));
    if (empty($entries)) {
        $result = [
            'ok' => false,
            'entries' => [],
            'error' => 'Blocklist file has no usable entries: ' . $filePath,
            'file' => $filePath,
        ];
        $cache[$cacheKey] = $result;
        return $result;
    }

    $result = [
        'ok' => true,
        'entries' => $entries,
        'error' => null,
        'file' => $filePath,
    ];
    $cache[$cacheKey] = $result;
    return $result;
}

function blocklist_configuration_status() {
    static $status = null;

    if ($status !== null) {
        return $status;
    }

    $errors = [];
    foreach (blocklist_supported_languages() as $lang) {
        foreach (['moderation', 'themes'] as $type) {
            $result = blocklist_load_entries($type, $lang);
            if (empty($result['ok'])) {
                $errors[] = (string)($result['error'] ?? ('Unknown blocklist error for ' . $type . '/' . $lang));
            }
        }
    }

    $status = [
        'ok' => empty($errors),
        'errors' => array_values(array_unique($errors)),
    ];

    return $status;
}

function blocklist_config_is_ready() {
    $status = blocklist_configuration_status();
    return !empty($status['ok']);
}

function blocklist_config_errors() {
    $status = blocklist_configuration_status();
    return $status['errors'] ?? [];
}
