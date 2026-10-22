<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

/** @var string $password_error задаётся в edit.php */

pageHeader('Вход в админ-панель', 'edit');
?>
<main class="flex-grow-1 py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-sm-8 col-md-6 col-lg-4">
    <div class="card bg-body-tertiary border-0">
        <div class="card-body p-4">
            <h1 class="h4 fw-bold mb-1">Админ-панель</h1>
            <p class="text-body-secondary small mb-4">Введите пароль администратора</p>
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="password" name="password" id="adminPassword" class="form-control"
                           placeholder="Пароль" required>
                    <label for="adminPassword">Пароль</label>
                </div>
                <button name="password_check" value="1" class="btn btn-warning w-100">Войти</button>
            </form>
            <?php if ($password_error !== ''): ?>
                <div class="alert alert-danger small mt-3 mb-0"><?= htmlspecialchars($password_error) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
</main>
<?php pageFooter(); ?>
