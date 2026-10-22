<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/layout.php';

session_start();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (loginUser(normalizeString($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''))) {
            header('Location: /');
            exit;
        }
        $errorMessage = 'Неправильный логин или пароль';
    } catch (PDOException $e) {
        $errorMessage = 'Ошибка базы данных, попробуйте позже.';
    }
}

pageHeader('Вход', 'login');
?>
<main class="flex-grow-1 py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
    <div class="card bg-body-tertiary border-0">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-1">Вход</h1>
            <p class="text-body-secondary mb-4">Войдите, чтобы читать отзывы покупателей</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email">Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Пароль" required>
                    <label for="password">Пароль</label>
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100">Войти</button>
            </form>

            <hr class="my-4">

            <p class="text-center small mb-2">
                Нет аккаунта? <a href="registration" class="link-warning">Зарегистрироваться</a>
            </p>
            <p class="text-center small mb-0">
                <a href="/" class="link-secondary"
                   onclick="return confirm('Все введённые данные будут потеряны. Вернуться на главную?')">
                    Вернуться на главную
                </a>
            </p>
        </div>
    </div>
</div>
</div>
</div>
</main>
<?php pageFooter(); ?>
