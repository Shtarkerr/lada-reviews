<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/review_repository.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../logic.php';

session_start();

$isLoggedIn = isset($_SESSION['user_id']);
['filters' => $filters, 'reviews' => $reviews, 'car_models' => $car_models] = indexPageData($_GET);

pageHeader('Автосалон Lada', 'reviews');
?>
<main class="flex-grow-1 py-4 py-lg-5">
<div class="container">

<?php if (!$isLoggedIn): ?>
    <!-- Гостевая страница -->
    <div class="row align-items-center g-5 py-lg-4 mb-5">
        <div class="col-lg-6">
            <p class="small text-uppercase text-warning fw-semibold mb-2">Отзывы покупателей</p>
            <h1 class="display-5 fw-bold lh-sm mb-4">Автосалон Lada</h1>
            <p class="lead text-body-secondary mb-4">
                Весь модельный ряд Lada в одном салоне. Читайте честные отзывы покупателей
                и ищите их по модели, имени автора, тексту и дате.
            </p>
            <div class="d-grid gap-2 d-sm-flex">
                <a href="autorisation" class="btn btn-warning btn-lg px-4">Войти</a>
                <a href="registration" class="btn btn-outline-light btn-lg px-4">Регистрация</a>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="images/salon.svg" alt="Автосалон Lada" class="img-fluid rounded-3">
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 g-md-5">
        <div class="col">
            <div class="border-top pt-4">
                <p class="small text-warning fw-semibold mb-2">01</p>
                <h2 class="h5 mb-3">Модельный ряд</h2>
                <p class="text-body-secondary mb-0">
                    <?= htmlspecialchars(implode(', ', array_column($car_models, 'name'))) ?>.
                </p>
            </div>
        </div>
        <div class="col">
            <div class="border-top pt-4">
                <p class="small text-warning fw-semibold mb-2">02</p>
                <h2 class="h5 mb-3">Тест-драйв и кредит</h2>
                <p class="text-body-secondary mb-0">
                    Тест-драйв в удобное время. Кредит или рассрочка прямо в салоне,
                    решение - в день обращения.
                </p>
            </div>
        </div>
        <div class="col">
            <div class="border-top pt-4">
                <p class="small text-warning fw-semibold mb-2">03</p>
                <h2 class="h5 mb-3">Trade-in и сервис</h2>
                <p class="text-body-secondary mb-0">
                    Честная оценка вашего автомобиля в зачёт нового.
                    ТО и гарантийный ремонт в нашем сервисном центре.
                </p>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Отзывы для авторизованного пользователя -->
    <div class="d-flex flex-wrap align-items-baseline gap-3 mb-4">
        <h1 class="h3 mb-0">Отзывы покупателей</h1>
        <span class="text-body-secondary">Найдено: <?= count($reviews) ?></span>
    </div>

    <div class="row g-4">
        <aside class="col-lg-4">
            <div class="card bg-body-tertiary border-0">
                <div class="card-body p-4">
                    <h2 class="small text-uppercase text-body-secondary fw-semibold mb-3">Фильтры</h2>
                    <form method="get" action="./">
                        <div class="mb-3">
                            <label for="filterModel" class="form-label small">Модель автомобиля</label>
                            <select name="car_model_id" id="filterModel" class="form-select">
                                <option value="" <?= $filters['car_model_id'] === '' ? 'selected' : '' ?>>
                                    Все модели
                                </option>
                                <?php foreach ($car_models as $model): ?>
                                    <?php
                                    $selected = $filters['car_model_id'] === (string)$model['id'] ? 'selected' : '';
                                    ?>
                                    <option value="<?= (int)$model['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars((string)$model['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="filterName" class="form-label small">Имя автора</label>
                            <input type="text" class="form-control" name="name" id="filterName"
                                   placeholder="Например, Анна"
                                   value="<?= htmlspecialchars($filters['name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="filterText" class="form-label small">Текст отзыва</label>
                            <input type="text" class="form-control" name="review_text" id="filterText"
                                   placeholder="Слово или фраза"
                                   value="<?= htmlspecialchars($filters['review_text']) ?>">
                        </div>

                        <div class="mb-4">
                            <label for="filterDate" class="form-label small">Дата отзыва</label>
                            <input type="date" class="form-control" name="review_date" id="filterDate"
                                   value="<?= htmlspecialchars($filters['review_date']) ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">Применить фильтр</button>
                            <a href="./" class="btn btn-outline-secondary">Очистить</a>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <section class="col-lg-8">
            <?php if (empty($reviews)): ?>
                <p class="text-body-secondary border rounded-3 p-4 mb-0">
                    Нет отзывов, соответствующих критериям фильтра.
                </p>
            <?php else: ?>
                <div class="row row-cols-1 g-3">
                    <?php foreach ($reviews as $review): ?>
                        <?php
                        $photo = (string)($review['img_path'] ?? '');
                        $hasPhoto = $photo !== '' && file_exists(__DIR__ . '/catalogue_images/' . $photo);
                        $initial = mb_strtoupper(mb_substr((string)$review['name'], 0, 1));
                        $modelName = (string)($review['car_model_name'] ?? 'Не указана');
                        ?>
                        <div class="col">
                            <article class="card bg-body-tertiary border-0">
                                <div class="card-body d-flex gap-3 p-4">
                                    <div class="flex-shrink-0">
                                        <?php if ($hasPhoto): ?>
                                            <img src="catalogue_images/<?= htmlspecialchars($photo) ?>" alt="Фото"
                                                 width="64" height="64" class="rounded-circle object-fit-cover">
                                        <?php else: ?>
                                            <div class="bg-body-secondary text-body-secondary rounded-circle
                                                        fs-4 lh-sm fw-semibold font-monospace px-4 py-3">
                                                <?= htmlspecialchars($initial) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                            <h2 class="h6 fw-semibold mb-0">
                                                <?= htmlspecialchars((string)$review['name']) ?>
                                            </h2>
                                            <small class="text-body-secondary">
                                                <?= htmlspecialchars(formatDate((string)$review['review_date'])) ?>
                                            </small>
                                        </div>
                                        <p class="small text-warning mb-2"><?= htmlspecialchars($modelName) ?></p>
                                        <p class="card-text text-body-secondary mb-0">
                                            <?= htmlspecialchars((string)$review['review_text']) ?>
                                        </p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php endif; ?>

</div>
</main>
<?php pageFooter(); ?>
