-- База сайта отзывов автосалона Lada (MySQL 8)

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `img_path` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `review_date` date NOT NULL,
  `car_model_id` int NOT NULL,
  `review_text` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `reviews` (`id`, `img_path`, `name`, `review_date`, `car_model_id`, `review_text`) VALUES
(1,	'1_2026-10-22_19-00-00.jpg',	'Сергей Кузнецов',	'2026-09-14',	2,	'Взял Vesta в комплектации Comfort. За год проехал 25 тысяч км, никаких проблем. Подвеска мягкая, в салоне просторно, расход по трассе около 7 литров. Менеджер в салоне всё подробно объяснил и помог с кредитом.'),
(2,	'2_2026-10-22_19-00-00.jpg',	'Анна Смирнова',	'2026-08-27',	1,	'Granta — первая моя машина. Недорогая, простая в обслуживании, запчасти есть везде. Не хватает только подлокотника. Оформили покупку за один день.'),
(3,	'3_2026-10-22_19-00-00.jpg',	'Дмитрий Волков',	'2026-07-03',	4,	'Niva Legend для рыбалки и дачи — то, что нужно. Проходимость отличная, по грязи и снегу едет уверенно. На трассе шумновато, но для такой машины это нормально.'),
(4,	'4_2026-10-22_19-00-00.jpg',	'Елена Морозова',	'2026-06-19',	3,	'Vesta SW Cross понравилась высоким клиренсом и большим багажником, вся семья с вещами помещается. Мультимедиа работает быстро, камера заднего вида очень выручает.'),
(5,	'5_2026-10-22_19-00-00.jpg',	'Игорь Павлов',	'2026-05-08',	6,	'Largus беру уже второй раз, для работы и семьи. Семь мест, огромный багажник, неубиваемая подвеска. Вежливый персонал, машину подготовили к выдаче вовремя.'),
(6,	'6_2026-10-22_19-00-00.jpg',	'Ольга Новикова',	'2026-10-02',	8,	'Iskra удивила: современный дизайн, удобная посадка, хорошая шумоизоляция для своего класса. Тест-драйв организовали в удобное для меня время.'),
(7,	'7_2026-10-22_19-00-00.jpg',	'Алексей Соколов',	'2026-02-11',	5,	'Niva Travel — хороший компромисс между внедорожником и городской машиной. Зимой не подводит, печка греет отлично. Минус — расход в городе около 12 литров.'),
(8,	'8_2026-10-22_19-00-00.jpg',	'Мария Лебедева',	'2026-03-30',	2,	'Vesta с вариатором — удобно ездить по городу в пробках. Сиденья с подогревом, руль тоже. Нареканий пока нет, на ТО в салоне всё сделали быстро.'),
(9,	'9_2026-10-22_19-00-00.jpg',	'Николай Козлов',	'2025-11-21',	1,	'Granta универсал для поездок на дачу. Цена устраивает, багажник вместительный. Хотелось бы получше шумоизоляцию, но за эти деньги грех жаловаться.'),
(10,	'10_2026-10-22_19-00-00.jpg',	'Татьяна Егорова',	'2025-12-15',	7,	'XRAY выбрала за высокую посадку и компактность — в городе легко парковаться. Обзорность хорошая, управлять легко.'),
(11,	'11_2026-10-22_19-00-00.jpg',	'Андрей Попов',	'2026-10-15',	9,	'Aura — самая комфортная Lada, на которой я ездил. Много места сзади, мягкий ход. Консультант честно рассказал про все плюсы и минусы комплектаций.'),
(12,	'12_2026-10-22_19-00-00.jpg',	'Виктория Орлова',	'2026-04-24',	3,	'Брали Vesta SW Cross в trade-in, старую машину оценили честно. Вся сделка заняла пару часов. Машиной довольна, особенно управляемостью на трассе.');

DROP TABLE IF EXISTS `car_models`;
CREATE TABLE `car_models` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `car_models` (`id`, `name`) VALUES
(1,	'Lada Granta'),
(2,	'Lada Vesta'),
(3,	'Lada Vesta SW Cross'),
(4,	'Lada Niva Legend'),
(5,	'Lada Niva Travel'),
(6,	'Lada Largus'),
(7,	'Lada XRAY'),
(8,	'Lada Iskra'),
(9,	'Lada Aura');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `FIO` varchar(255) DEFAULT NULL,
  `date` varchar(10) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `interes` varchar(255) DEFAULT NULL,
  `vk` varchar(255) DEFAULT NULL,
  `blood` varchar(3) DEFAULT NULL,
  `resus` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `FIO`, `date`, `address`, `sex`, `interes`, `vk`, `blood`, `resus`, `email`, `password`) VALUES
(6,	'Иванов Иван Иванович',	'2001-03-02',	'Москва',	'М',	'sdfghjkl;',	'https://vk.com/feed',	'I',	'+',	'email11@gmail.com',	'$2y$10$olY28NnXeXyae314jY3ese.EhQBaI1OQG0PGxzOl1.3ki91YbxlUy'),
(7,	'Попов Дмитрий Александрович',	'31/07/2005',	'Волгоград, ...',	'М',	'-',	'https://vk.com/axawys',	'I',	'+',	'axawys@gmail.com',	'$2y$10$abcOclz.22MohHsozeS50OKY9l/3fUDU4Wu3JEoFzA6V.g3VZQ6Nq');
