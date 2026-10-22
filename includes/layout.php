<?php

declare(strict_types=1);

/**
 * Начало страницы: <head> с Bootstrap, открытый <body> и верхнее меню.
 *
 * @param string $activeNav пункт меню, который подсвечивается: 'reviews', 'edit', 'login', 'registration'
 */
function pageHeader(string $title, string $activeNav = ''): void
{
    $title = htmlspecialchars($title);
    $nav = navbar($activeNav);

    echo <<<HTML
        <!DOCTYPE html>
        <html lang="ru" data-bs-theme="dark">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$title}</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex flex-column min-vh-100 bg-body">
        {$nav}

        HTML;
}

// Верхнее меню: у гостя - вход и регистрация, у вошедшего пользователя - отзывы, админка и выход
function navbar(string $activeNav): string
{
    $item = static function (string $key, string $href, string $text) use ($activeNav): string {
        $active = $key === $activeNav ? ' active" aria-current="page' : '';
        return "<li class=\"nav-item\"><a class=\"nav-link{$active}\" href=\"{$href}\">{$text}</a></li>";
    };

    if (isset($_SESSION['user_id'])) {
        $links = $item('reviews', '/', 'Отзывы') . $item('edit', 'edit', 'Админ-панель');
        $email = htmlspecialchars((string)($_SESSION['email'] ?? ''));
        $actions = "<span class=\"navbar-text small me-md-2\">{$email}</span>"
            . '<a href="logout" class="btn btn-sm btn-outline-secondary">Выйти</a>';
    } else {
        $links = $item('reviews', '/', 'Главная');
        $actions = '<a href="autorisation" class="btn btn-sm btn-warning">Войти</a>'
            . '<a href="registration" class="btn btn-sm btn-outline-secondary">Регистрация</a>';
    }

    return <<<HTML
        <nav class="navbar navbar-expand-md bg-body border-bottom">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <span class="fw-bold text-warning">LADA</span> <span class="fw-light">Автосалон</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Меню">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto">{$links}</ul>
                    <div class="d-flex flex-wrap align-items-center gap-2 py-2 py-md-0">{$actions}</div>
                </div>
            </div>
        </nav>
        HTML;
}

/**
 * Конец страницы: подвал, скрипты и закрывающие теги. Bootstrap JS подключается всегда (меню, модальные окна).
 *
 * @param list<string> $scripts пути к JS-файлам относительно public/
 */
function pageFooter(array $scripts = []): void
{
    echo <<<HTML
        <footer class="text-body-secondary border-top py-4 mt-5">
            <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small">
                <span><strong class="text-body">LADA</strong> Автосалон</span>
                <span>Ежедневно с 9:00 до 21:00</span>
            </div>
        </footer>

        HTML;
    foreach (['js/bootstrap.bundle.min.js', ...$scripts] as $script) {
        echo '<script src="' . htmlspecialchars($script) . "\"></script>\n";
    }
    echo "</body>\n</html>\n";
}
