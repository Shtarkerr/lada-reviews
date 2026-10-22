<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    jsonResponse(['error' => 'Invalid request'], 400);
}

try {
    jsonResponse(['exists' => emailExists(normalizeString($_POST['email']))]);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
