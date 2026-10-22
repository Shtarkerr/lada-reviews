<?php

declare(strict_types=1);

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        jsonResponse([
            'success' => false,
            'error' => 'INVALID_JSON',
            'message' => 'Некорректный JSON в теле запроса.'
        ], 400);
    }

    return $data;
}

function normalizeString(mixed $value): string
{
    return trim((string)($value ?? ''));
}

// Дата в формате ГГГГ-ММ-ДД (так её отдаёт <input type="date">). Неверная дата превращается в ''.
function normalizeDate(mixed $value): string
{
    $value = normalizeString($value);
    $date = DateTime::createFromFormat('!Y-m-d', $value);

    return $date && $date->format('Y-m-d') === $value ? $value : '';
}

// ГГГГ-ММ-ДД -> ДД.ММ.ГГГГ для вывода на странице
function formatDate(string $date): string
{
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);

    return $parsed ? $parsed->format('d.m.Y') : $date;
}

function validateReviewPayload(array $data): array
{
    $errors = [];
    $today = date('Y-m-d');

    $name = normalizeString($data['name'] ?? '');
    $reviewDate = normalizeDate($data['review_date'] ?? '');
    $carModelId = filter_var($data['car_model_id'] ?? '', FILTER_VALIDATE_INT);
    $reviewText = normalizeString($data['review_text'] ?? '');

    if ($name === '' || mb_strlen($name) > 45) {
        $errors['name'] = 'Имя обязательно, не длиннее 45 символов.';
    }

    if ($reviewDate === '' || $reviewDate < '2000-01-01' || $reviewDate > $today) {
        $errors['review_date'] = 'Дата отзыва должна быть от 01.01.2000 до сегодняшнего дня.';
    }

    if ($carModelId === false || $carModelId < 1) {
        $errors['car_model_id'] = 'Выберите модель автомобиля.';
    }

    if ($reviewText === '' || mb_strlen($reviewText) > 1000) {
        $errors['review_text'] = 'Текст отзыва обязателен, не длиннее 1000 символов.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'review_date' => $reviewDate,
            'car_model_id' => $carModelId,
            'review_text' => $reviewText,
        ]
    ];
}

const REVIEW_PHOTOS_DIR = __DIR__ . '/../public/catalogue_images';

const REVIEW_PHOTO_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];

// Имя файла фото: <id отзыва>_<время загрузки>.<расширение>, например 16_2026-03-19_00-13-33.jpg
function reviewPhotoFileName(int $reviewId, string $extension, ?int $uploadedAt = null): string
{
    return sprintf('%d_%s.%s', $reviewId, date('Y-m-d_H-i-s', $uploadedAt ?? time()), $extension);
}

function deleteReviewPhoto(string $fileName): void
{
    $fileName = basename($fileName);
    if ($fileName === '') {
        return;
    }

    $path = REVIEW_PHOTOS_DIR . '/' . $fileName;
    if (is_file($path)) {
        unlink($path);
    }
}

function requireAdminAccess(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['edit_page_access'])) {
        jsonResponse([
            'success' => false,
            'error' => 'UNAUTHORIZED',
            'message' => 'Требуется доступ администратора.'
        ], 401);
    }
}

// Вход в админку по паролю из ADMIN_PASSWORD_HASH_B64. Возвращает текст ошибки или '' при успехе.
function loginAdmin(string $password): string
{
    $hashB64 = getenv('ADMIN_PASSWORD_HASH_B64');
    $adminHash = $hashB64 ? base64_decode($hashB64, true) : false;

    if (!$adminHash) {
        return 'Не задан ADMIN_PASSWORD_HASH_B64';
    }
    if (!password_verify($password, $adminHash)) {
        return 'Неверный пароль';
    }

    session_regenerate_id(true);
    $_SESSION['edit_page_access'] = true;

    return '';
}

// Вход пользователя по email и паролю. При успехе записывает пользователя в сессию.
function loginUser(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];

    return true;
}

function emailExists(string $email): bool
{
    $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ?');
    $stmt->execute([$email]);

    return $stmt->fetchColumn() !== false;
}

/**
 * Проверка формы регистрации.
 *
 * @return array{errors: list<string>, data: array<string, string>}
 */
function validateRegistration(array $input): array
{
    $data = [];
    foreach (['FIO', 'date', 'address', 'sex', 'interes', 'vk', 'blood', 'resus', 'email'] as $field) {
        $data[$field] = normalizeString($input[$field] ?? '');
    }
    $password = (string)($input['password'] ?? '');
    $confirmPassword = (string)($input['confirmPassword'] ?? '');

    $errors = [];
    if ($data['FIO'] === '') {
        $errors[] = 'ФИО обязательно.';
    }
    if ($data['date'] === '') {
        $errors[] = 'Дата рождения обязательна.';
    }
    if ($data['address'] === '') {
        $errors[] = 'Адрес обязателен.';
    }
    if (!in_array($data['sex'], ['М', 'Ж'], true)) {
        $errors[] = 'Пол некорректен.';
    }
    if ($data['interes'] === '') {
        $errors[] = 'Интересы обязательны.';
    }
    if ($data['vk'] !== '' && !filter_var($data['vk'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Некорректная ссылка VK.';
    }
    if ($data['blood'] === '') {
        $errors[] = 'Группа крови обязательна.';
    }
    if (!in_array($data['resus'], ['+', '-'], true)) {
        $errors[] = 'Резус-фактор некорректен.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email.';
    } elseif (emailExists($data['email'])) {
        $errors[] = 'Эта почта уже используется';
    }
    if (strlen($password) < 7) {
        $errors[] = 'Пароль минимум 7 символов.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Пароли не совпадают.';
    }

    $data['password'] = $password;

    return ['errors' => $errors, 'data' => $data];
}

/**
 * @param array<string, string> $data данные из validateRegistration()
 */
function registerUser(array $data): void
{
    $stmt = db()->prepare(
        'INSERT INTO users (FIO, date, address, sex, interes, vk, blood, resus, email, password)
         VALUES (:FIO, :date, :address, :sex, :interes, :vk, :blood, :resus, :email, :password)'
    );
    $stmt->execute([
        ':FIO' => $data['FIO'],
        ':date' => $data['date'],
        ':address' => $data['address'],
        ':sex' => $data['sex'],
        ':interes' => $data['interes'],
        ':vk' => $data['vk'],
        ':blood' => $data['blood'],
        ':resus' => $data['resus'],
        ':email' => $data['email'],
        ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
    ]);
}
