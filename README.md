# Отзывы автосалона Lada

Сайт отзывов покупателей автосалона Lada (лабораторная работа).

Гость видит описание салона и может зарегистрироваться. После входа открывается список отзывов с фильтрами:

- по модели автомобиля (выпадающий список);
- по имени автора;
- по тексту отзыва;
- по дате отзыва.

Отзывы добавляют, редактируют и удаляют в админ-панели. Там же загружаются фото и делается бэкап базы.

## Технологии

- PHP 8.2 (без фреймворков, `declare(strict_types=1)`), PDO
- MySQL 8
- Bootstrap 5.3 (тёмная тема, только стандартные классы) и обычный JavaScript
- Docker Compose: Apache + PHP, MySQL, Adminer
- Стиль кода: PSR-12 (phpcs)

## Запуск

```sh
docker compose up --build -d
```

При первом запуске (и после `down -v`) MySQL 20–40 секунд загружает `lada.sql`. Контейнер сайта ждёт готовности базы (`healthcheck` в `docker-compose.yml`), поэтому команда `up` в этот раз завершается не сразу.

Apache в контейнере работает с uid/gid 1000, чтобы записывать загруженные фото в примонтированную папку. Если uid пользователя не 1000, собрать так:

```sh
docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
```

### Адреса

| Адрес | Что это |
|---|---|
| http://localhost:8080 | сайт |
| http://localhost:8081 | Adminer (сервер `db`, пользователь `user`, пароль `password`, база `appdb`) |

### Перезапуск

```sh
docker compose down
docker compose up --build -d
```

### Полный сброс базы (удалит все данные)

```sh
docker compose down -v
docker compose up --build -d
```

## Админ-панель (`/edit`)

Вход по отдельному паролю, по умолчанию `1234`. Его хэш задаётся в `docker-compose.yml` (`ADMIN_PASSWORD_HASH_B64`). Сгенерировать хэш для нового пароля:

```sh
php -r 'echo base64_encode(password_hash("новый_пароль", PASSWORD_DEFAULT));'
```

### API

Каждая операция с отзывами — отдельный файл и отдельный JSON-запрос (видно в DevTools → Network → Fetch/XHR):

| Запрос | Метод | Что делает |
|---|---|---|
| `api/read` | GET | список отзывов; принимает те же фильтры, что главная: `car_model_id`, `name`, `review_text`, `review_date=ГГГГ-ММ-ДД` |
| `api/create` | POST | добавление |
| `api/update` | POST | изменение |
| `api/delete` | POST | удаление |
| `api/get_car_models` | GET | список моделей Lada |
| `api/upload_photo` | POST | загрузка фото (multipart: `id`, `photo`) |

Бэкап: кнопки «Скачать бэкап» (`/export`) и «Загрузить бэкап» (`/import`).

## База данных

`lada.sql` загружается автоматически при первом запуске. Таблицы:

| Таблица | Содержимое |
|---|---|
| `reviews` | отзывы: имя автора, дата отзыва, модель, текст, фото |
| `car_models` | модели Lada для выпадающего списка |
| `users` | зарегистрированные пользователи |

Если таблиц нет или в базе остались старые (база была создана раньше), сделать полный сброс (см. выше) или загрузить дамп вручную:

```sh
docker compose exec -T db mysql -uuser -ppassword appdb < lada.sql
```

Проверить:

```sh
docker compose exec db mysql -uuser -ppassword appdb -e "SHOW TABLES"
```

## Фото к отзывам

Хранятся в `public/catalogue_images/`, имя файла: `<id отзыва>_<время загрузки>.<расширение>`, например `16_2026-03-19_00-13-33.jpg`. Загружаются из админки при добавлении или редактировании отзыва. При замене фото и при удалении отзыва старый файл удаляется.

Фото к отзывам из `lada.sql` — лица, сгенерированные нейросетью ([thispersondoesnotexist.com](https://thispersondoesnotexist.com)), это не реальные люди. Уменьшены до 256×256.

## Структура

```
public/                  корень сайта (DocumentRoot Apache), снаружи доступна только эта папка
  index.php                главная: гостевая страница или отзывы с фильтрами
  autorisation.php         вход
  registration.php         регистрация
  edit.php                 админ-панель
  export.php, import.php   бэкап базы
  api/                     JSON-API админки
  js/                      edit.js (админка), registration.js (проверка формы)
  images/, catalogue_images/
includes/                общий PHP-код, подключается из public/ через require
  helpers.php              вход, регистрация, проверка данных, JSON-ответы
  review_repository.php    запросы к отзывам и моделям автомобилей
  layout.php               pageHeader()/pageFooter(): общие <head>, меню и подвал
db.php                   подключение к базе
logic.php                фильтры из запроса и данные для главной
lada.sql                 структура базы и начальные данные
docker/                  настройки Apache и PHP
```

Новые страницы и статические файлы класть в `public/`, всё служебное — вне её.

## Проверка кода

```sh
phpcs     # стиль PSR-12, правила в phpcs.xml (запускать из корня проекта)
phpcbf    # автоисправление того, что нашёл phpcs
```
