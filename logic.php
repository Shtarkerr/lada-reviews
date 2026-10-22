<?php

declare(strict_types=1);

/**
 * Фильтры отзывов из GET-запроса. Дата отзыва в неверном формате отбрасывается.
 *
 * @return array{car_model_id: string, name: string, review_text: string, review_date: string}
 */
function reviewFilters(array $query): array
{
    return [
        'car_model_id' => normalizeString($query['car_model_id'] ?? ''),
        'name' => normalizeString($query['name'] ?? ''),
        'review_text' => normalizeString($query['review_text'] ?? ''),
        'review_date' => normalizeDate($query['review_date'] ?? ''),
    ];
}

/**
 * Данные для главной страницы: фильтры из запроса, отфильтрованные отзывы и список моделей.
 *
 * @return array{
 *     filters: array{car_model_id: string, name: string, review_text: string, review_date: string},
 *     reviews: array,
 *     car_models: array
 * }
 */
function indexPageData(array $query): array
{
    $filters = reviewFilters($query);

    return [
        'filters' => $filters,
        'reviews' => getReviews(db(), $filters),
        'car_models' => getAllCarModels(db()),
    ];
}
