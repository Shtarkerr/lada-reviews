<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/layout.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ['errors' => $errors, 'data' => $data] = validateRegistration($_POST);
        if ($errors === []) {
            registerUser($data);
            header('Location: autorisation');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = 'Ошибка базы данных, попробуйте позже.';
    }
}

pageHeader('Регистрация', 'registration');
?>
<main class="flex-grow-1 py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-10 col-xl-8">
<div class="card bg-body-tertiary border-0">
<div class="card-body p-4 p-md-5">
<h1 class="h3 fw-bold mb-1">Регистрация</h1>
<p class="text-body-secondary mb-4">Создайте аккаунт, чтобы получить доступ к отзывам</p>
<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
<form method="POST" id="regForm">
    <h2 class="small text-uppercase text-body-secondary fw-semibold border-bottom pb-2 mb-3">Личные данные</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="FIO" class="form-label">ФИО</label>
            <input type="text" class="form-control" name="FIO" id="FIO" required>
            <small class="text-danger d-none">Введите ФИО</small>
        </div>

        <div class="col-md-6">
            <label for="day" class="form-label">Дата рождения</label>
            <div class="input-group">
                <select id="day" class="form-select" required></select>
                <select id="month" class="form-select" required></select>
                <select id="year" class="form-select" required></select>
            </div>
            <small id="dobError" class="text-danger d-none">Выберите корректную дату</small>
            <input type="hidden" id="dob" name="date">
        </div>

        <div class="col-md-6">
            <label for="sex" class="form-label">Пол</label>
            <select class="form-select" name="sex" id="sex" required>
                <option value="">Выберите пол</option>
                <option value="М">Мужской</option>
                <option value="Ж">Женский</option>
            </select>
            <small class="text-danger d-none" id="sexError">Выберите пол</small>
        </div>

        <div class="col-md-6">
            <label for="address" class="form-label">Адрес</label>
            <input type="text" class="form-control" name="address" id="address" required>
            <small class="text-danger d-none">Введите адрес</small>
        </div>

        <div class="col-md-6">
            <label for="blood" class="form-label">Группа крови</label>
            <select class="form-select" name="blood" id="blood" required>
                <option value="">Выберите группу</option>
                <option value="I">I (0)</option>
                <option value="II">II (A)</option>
                <option value="III">III (B)</option>
                <option value="IV">IV (AB)</option>
            </select>
            <small class="text-danger d-none" id="bloodError">Выберите группу крови</small>
        </div>

        <div class="col-md-6">
            <label for="resus" class="form-label">Резус-фактор</label>
            <select class="form-select" name="resus" id="resus" required>
                <option value="">Выберите</option>
                <option value="+">Положительный (+)</option>
                <option value="-">Отрицательный (-)</option>
            </select>
            <small class="text-danger d-none" id="resusError">Выберите резус-фактор</small>
        </div>

        <div class="col-12">
            <label for="interes" class="form-label">Интересы</label>
            <textarea class="form-control" name="interes" id="interes" rows="3" required></textarea>
            <small class="text-danger d-none">Введите интересы</small>
        </div>
    </div>

    <h2 class="small text-uppercase text-body-secondary fw-semibold border-bottom pb-2 mb-3">Аккаунт</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
            <small class="text-danger d-none" id="emailFormatError">Введите корректный email</small>
            <small class="text-danger d-none" id="emailError"></small>
        </div>

        <div class="col-md-6">
            <label for="vk" class="form-label">Ссылка VK</label>
            <input type="url" class="form-control" name="vk" id="vk" placeholder="https://vk.com/..." required>
            <small class="text-danger d-none">Введите корректную ссылку</small>
        </div>

        <div class="col-md-6">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" name="password" id="password" required>
            <small class="text-danger d-none">Пароль минимум 7 символов</small>
        </div>

        <div class="col-md-6">
            <label for="confirmPassword" class="form-label">Подтвердите пароль</label>
            <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
            <small class="text-danger d-none">Пароли должны совпадать</small>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <a href="/" class="link-secondary"
           onclick="return confirm('Все заполненные данные будут потеряны. Вернуться на главную?')">
            Вернуться на главную
        </a>
        <button type="submit" class="btn btn-warning btn-lg px-5" id="submitBtn">Зарегистрироваться</button>
    </div>
</form>
</div>
</div>
</div>
</div>
</div>
</main>
<?php pageFooter(['js/registration.js']); ?>
