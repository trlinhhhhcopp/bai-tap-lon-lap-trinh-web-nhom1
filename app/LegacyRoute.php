<?php

function dispatchLegacyRoute(string $route): void
{
    require_once __DIR__ . '/bootstrap.php';

    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $applicationRoot = realpath(__DIR__ . '/..') ?: '';
    $basePath = '';
    if ($documentRoot !== '' && $applicationRoot !== '') {
        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $applicationRoot = str_replace('\\', '/', $applicationRoot);
        if ($applicationRoot === $documentRoot) {
            $basePath = '';
        } elseif (str_starts_with($applicationRoot, $documentRoot . '/')) {
            $basePath = '/' . trim(substr($applicationRoot, strlen($documentRoot)), '/');
        }
    }

    $query = $_SERVER['QUERY_STRING'] ?? '';
    $_SERVER['REQUEST_URI'] = $basePath . $route . ($query !== '' ? '?' . $query : '');
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';

    \App\bootstrapRouter()->dispatch();
}
