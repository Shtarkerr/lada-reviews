<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/layout.php';

session_start();

$password_error = '';
if (isset($_POST['password_check'])) {
    $password_error = loginAdmin((string)($_POST['password'] ?? ''));
    if ($password_error === '') {
        header('Location: edit');
        exit;
    }
}

if (empty($_SESSION['edit_page_access'])) {
    require __DIR__ . '/../includes/admin_login.php';
    exit;
}

$backupMessages = [
    'ok' => ['success', 'Бэкап успешно загружен.'],
    'error' => ['danger', 'Не удалось загрузить бэкап.'],
];
$backupMessage = $backupMessages[$_GET['backup'] ?? ''] ?? null;

pageHeader('Админ-панель', 'edit');
?>
<main class="flex-grow-1 py-4">
<div class="container">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Управление отзывами</h1>
        <p class="text-body-secondary mb-0">Добавление, изменение и удаление отзывов покупателей</p>
    </div>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addModal">
        + Добавить отзыв
    </button>
</div>

<?php if ($backupMessage): ?>
<div class="alert alert-<?= $backupMessage[0] ?>"><?= htmlspecialchars($backupMessage[1]) ?></div>
<?php endif; ?>
<div id="apiMessage"></div>

<div class="card bg-body-tertiary border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-transparent mb-0">
            <thead class="small text-uppercase">
                <tr>
                    <th scope="col" class="text-body-secondary fw-semibold">Фото</th>
                    <th scope="col" class="text-body-secondary fw-semibold">Автор</th>
                    <th scope="col" class="text-body-secondary fw-semibold">Модель</th>
                    <th scope="col" class="text-body-secondary fw-semibold">Дата</th>
                    <th scope="col" class="text-body-secondary fw-semibold">Отзыв</th>
                    <th scope="col" class="text-body-secondary fw-semibold text-end">Действия</th>
                </tr>
            </thead>
            <tbody id="reviews"></tbody>
        </table>
    </div>
</div>

<div class="card bg-body-tertiary border-0">
    <div class="card-body">
        <h2 class="small text-uppercase text-body-secondary fw-semibold mb-3">Резервная копия базы</h2>
        <form action="import" method="POST" enctype="multipart/form-data"
              class="d-flex flex-column flex-md-row gap-2">
            <a href="export" class="btn btn-outline-light text-nowrap">Скачать бэкап</a>
            <div class="input-group">
                <input type="file" name="sql_file" accept=".sql" class="form-control" required>
                <button class="btn btn-outline-secondary"
                        onclick="return confirm('Текущие данные будут заменены данными из бэкапа. Продолжить?')">
                    Загрузить бэкап
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</main>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новый отзыв</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="add_name" class="form-label">Имя автора</label>
                        <input id="add_name" class="form-control">
                    </div>
                    <div class="col-sm-6">
                        <label for="add_car_model" class="form-label">Модель</label>
                        <select id="add_car_model" class="form-select"></select>
                    </div>
                    <div class="col-sm-6">
                        <label for="add_review_date" class="form-label">Дата отзыва</label>
                        <input id="add_review_date" type="date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="add_text" class="form-label">Текст отзыва</label>
                        <textarea id="add_text" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-12">
                        <label for="add_photo" class="form-label">Фотография</label>
                        <input id="add_photo" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                <button class="btn btn-warning" onclick="createReview()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование отзыва</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="edit_name" class="form-label">Имя автора</label>
                        <input id="edit_name" class="form-control">
                    </div>
                    <div class="col-sm-6">
                        <label for="edit_car_model" class="form-label">Модель</label>
                        <select id="edit_car_model" class="form-select"></select>
                    </div>
                    <div class="col-sm-6">
                        <label for="edit_review_date" class="form-label">Дата отзыва</label>
                        <input id="edit_review_date" type="date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="edit_text" class="form-label">Текст отзыва</label>
                        <textarea id="edit_text" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-12">
                        <label for="edit_photo" class="form-label">Новая фотография (необязательно)</label>
                        <input id="edit_photo" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                <button class="btn btn-warning" onclick="updateReview()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<?php pageFooter(['js/edit.js']); ?>
