<?php

declare(strict_types=1);

function getAllCarModels(PDO $connect): array
{
    $stmt = $connect->query('SELECT id, name FROM car_models ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function carModelExists(PDO $connect, int $id): bool
{
    $stmt = $connect->prepare('SELECT COUNT(*) FROM car_models WHERE id = ?');
    $stmt->execute([$id]);
    return (bool)$stmt->fetchColumn();
}

function findReviewById(PDO $connect, int $id): ?array
{
    $stmt = $connect->prepare('SELECT * FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    return $review ?: null;
}

function getReviews(PDO $connect, array $filters = []): array
{
    $sql = "SELECT r.*, m.name AS car_model_name
            FROM reviews r
            LEFT JOIN car_models m ON r.car_model_id = m.id";

    $where = [];
    $params = [];

    if (!empty($filters['car_model_id'])) {
        $where[] = 'r.car_model_id = :car_model_id';
        $params[':car_model_id'] = (int)$filters['car_model_id'];
    }

    if (!empty($filters['name'])) {
        $where[] = 'r.name LIKE :name';
        $params[':name'] = '%' . $filters['name'] . '%';
    }

    if (!empty($filters['review_text'])) {
        $where[] = 'r.review_text LIKE :review_text';
        $params[':review_text'] = '%' . $filters['review_text'] . '%';
    }

    if (!empty($filters['review_date'])) {
        $where[] = 'r.review_date = :review_date';
        $params[':review_date'] = $filters['review_date'];
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY r.review_date DESC, r.id DESC';

    $stmt = $connect->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
