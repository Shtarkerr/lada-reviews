<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'METHOD_NOT_ALLOWED', 'message' => 'Используйте GET.'], 405);
}

try {
    jsonResponse([
        'success' => true,
        'data' => getReviews(db(), reviewFilters($_GET))
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'error' => 'SERVER_ERROR',
        'message' => 'Не удалось получить список отзывов.',
    ], 500);
}
