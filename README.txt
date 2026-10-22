Сайт отзывов покупателей автосалона Lada.
Гость видит описание салона; после входа - таблица отзывов с фильтрами
по модели автомобиля (выпадающий список), имени автора, тексту отзыва и дате отзыва.

Запуск
    docker compose up --build -d

    При первом запуске (и после down -v) MySQL 20-40 секунд загружает lada.sql.
    Контейнер сайта ждёт готовности базы (healthcheck в docker-compose.yml),
    поэтому команда up в этот раз завершается не сразу.

    Apache в контейнере работает с uid/gid 1000, чтобы записывать загруженные фото
    в примонтированную папку. Если uid пользователя не 1000, собрать так:
        docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)

Адреса
    http://localhost:8080  - сайт
    http://localhost:8081  - Adminer (сервер: db, пользователь: user, пароль: password, база: appdb)

Админ-панель (/edit)
    Вход по отдельному паролю. Его хэш задаётся в docker-compose.yml
    (ADMIN_PASSWORD_HASH_B64). Сгенерировать новый:
    php -r 'echo base64_encode(password_hash("новый_пароль", PASSWORD_DEFAULT));'

    CRUD отзывов: каждая операция в своём файле, отдельный JSON-запрос
    (видно в DevTools -> Network -> Fetch/XHR):
        api/read    GET   - список отзывов (те же фильтры, что на главной:
                            car_model_id, name, review_text, review_date=ГГГГ-ММ-ДД)
        api/create  POST  - добавление
        api/update  POST  - изменение
        api/delete  POST  - удаление
        api/get_car_models GET - список моделей Lada
        api/upload_photo POST - загрузка фото (multipart: id, photo)

    Бэкап: кнопки "Скачать бэкап" (/export) и "Загрузить бэкап" (/import).

База данных
    lada.sql загружается автоматически при первом запуске. Таблицы:
        reviews    - отзывы (имя автора, дата отзыва, модель, текст, фото)
        car_models - модели Lada для выпадающего списка
        users      - зарегистрированные пользователи
    Если таблиц нет или в базе остались старые (база уже была создана раньше),
    сделать полный сброс (см. ниже) или загрузить вручную:
        docker compose exec -T db mysql -uuser -ppassword appdb < lada.sql
    Проверить:
        docker compose exec db mysql -uuser -ppassword appdb -e "SHOW TABLES"

Перезапуск
    docker compose down
    docker compose up --build -d

Полный сброс базы (удалит все данные)
    docker compose down -v
    docker compose up --build -d

Фото к отзывам
    Хранятся в public/catalogue_images/, имя файла: <id отзыва>_<время загрузки>.<расширение>,
    например 16_2026-03-19_00-13-33.jpg. Загружаются из админки (/edit) при добавлении
    или редактировании отзыва; старое фото при замене и при удалении отзыва удаляется.
    Фото к отзывам из lada.sql - лица, сгенерированные нейросетью (thispersondoesnotexist.com),
    это не реальные люди. Уменьшены до 256x256.

Структура
    public/   - корень сайта (DocumentRoot Apache): страницы, api/, css/, js/, images/,
                catalogue_images/. Снаружи доступно только содержимое этой папки.
    includes/, db.php, logic.php - общий PHP-код, подключается из public/ через require:
        helpers.php - вход, регистрация, проверка данных, JSON-ответы
        review_repository.php - запросы к отзывам и моделям автомобилей
        layout.php - pageHeader()/pageFooter(): общие <head> и подвал страниц
    public/js/ - скрипты страниц (edit.js, registration.js)
    lada.sql, docker/ - база и окружение, через веб недоступны.
    Новые страницы и статические файлы класть в public/, всё служебное - вне её.

Проверка кода
    phpcs        - стиль PSR12, правила в phpcs.xml (запускать из корня проекта)
    phpcbf       - автоисправление того, что нашёл phpcs
