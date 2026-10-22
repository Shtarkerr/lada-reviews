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

$review = findReviewById(db(), $id);
if (!$review) {
    jsonResponse(['success' => false, 'error' => 'NOT_FOUND', 'message' => 'Отзыв не найден.'], 404);
}

try {
    $stmt = db()->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
    deleteReviewPhoto($review['img_path']);

    jsonResponse(['success' => true, 'message' => 'Отзыв удалён.', 'data' => ['id' => $id]]);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'Не удалось удалить отзыв.'], 500);
}
