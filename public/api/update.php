<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireAdminAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'METHOD_NOT_ALLOWED', 'message' => 'Используйте POST.'], 405);
}

$data = getJsonInput();
$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id < 1) {
    jsonResponse([
        'success' => false,
        'error' => 'VALIDATION_ERROR',
        'message' => 'Поле id обязательно и должно быть положительным числом.',
    ], 422);
}

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

if (!findReviewById(db(), $id)) {
    jsonResponse(['success' => false, 'error' => 'NOT_FOUND', 'message' => 'Отзыв не найден.'], 404);
}

try {
    $stmt = db()->prepare(
        'UPDATE reviews SET name = ?, review_date = ?, car_model_id = ?, review_text = ? WHERE id = ?'
    );
    $stmt->execute([
        $payload['name'],
        $payload['review_date'],
        $payload['car_model_id'],
        $payload['review_text'],
        $id
    ]);

    jsonResponse(['success' => true, 'message' => 'Отзыв обновлён.', 'data' => ['id' => $id]]);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'Не удалось обновить отзыв.'], 500);
}
