<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdminAccess();

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_FILES['sql_file'])
    || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK
) {
    header('Location: edit?backup=error');
    exit;
}

try {
    $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
    if ($sql === false) {
        throw new RuntimeException('Не удалось прочитать файл бэкапа');
    }
    db()->exec($sql);
    header('Location: edit?backup=ok');
} catch (PDOException | RuntimeException $e) {
    header('Location: edit?backup=error');
}
exit;
