<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireAdminAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'METHOD_NOT_ALLOWED', 'message' => 'Используйте POST.'], 405);
}

$id = (int)($_POST['id'] ?? 0);
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

$file = $_FILES['photo'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $tooLarge = $file && in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_FILE',
        'message' => $tooLarge ? 'Файл слишком большой.' : 'Файл фотографии не получен.'
    ], 422);
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
$extension = REVIEW_PHOTO_TYPES[$mimeType] ?? null;
if ($extension === null) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_FILE',
        'message' => 'Допустимые форматы: JPEG, PNG, WebP, GIF.',
    ], 422);
}

$fileName = reviewPhotoFileName($id, $extension);
if (!move_uploaded_file($file['tmp_name'], REVIEW_PHOTOS_DIR . '/' . $fileName)) {
    jsonResponse(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'Не удалось сохранить фотографию.'], 500);
}

try {
    $stmt = db()->prepare('UPDATE reviews SET img_path = ? WHERE id = ?');
    $stmt->execute([$fileName, $id]);
} catch (Throwable $e) {
    deleteReviewPhoto($fileName);
    jsonResponse(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'Не удалось сохранить фотографию.'], 500);
}

if ($review['img_path'] !== $fileName) {
    deleteReviewPhoto($review['img_path']);
}

jsonResponse([
    'success' => true,
    'message' => 'Фотография загружена.',
    'data' => ['id' => $id, 'img_path' => $fileName],
]);
