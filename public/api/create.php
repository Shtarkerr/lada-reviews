<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireAdminAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'METHOD_NOT_ALLOWED', 'message' => 'Используйте POST.'], 405);
}

$data = getJsonInput();
$validation = validateReviewPayload($data);
if ($validation['errors']) {
    jsonResponse([
        'success' => false,
        'error' => 'VALIDATION_ERROR',
        'message' => 'Ошибка валидации входных данных.',
        'fields' => $validation['errors']
    ], 422);
}

$payload = $validation['data'];
if (!carModelExists(db(), $payload['car_model_id'])) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_CAR_MODEL',
        'message' => 'Указанная модель не найдена.',
    ], 422);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO reviews (img_path, name, review_date, car_model_id, review_text) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        '',
        $payload['name'],
        $payload['review_date'],
        $payload['car_model_id'],
        $payload['review_text']
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Отзыв добавлен.',
        'data' => ['id' => (int)db()->lastInsertId()]
    ], 201);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'Не удалось добавить отзыв.'], 500);
}
