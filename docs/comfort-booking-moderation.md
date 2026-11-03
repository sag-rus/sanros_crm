# Панель Comfort Booking

Пункт после TL Content API. Название объекта открывает публичный URL в новой вкладке. Поиск без сохранения результатов в CRM: название, полная цепочка направления, ID сайта и ID Comfort Booking. Порядок задаёт API сайта: pending → approved → disabled, дата создания по убыванию внутри группы.

Объекты и состояние не копируются в БД/кеш CRM. В PHP-сессии только CSRF. Данные таблицы живут в памяти браузера, ответы no-store. Dedicated endpoint comfort_booking.php не использует legacy mysql.php dispatch/history_global. Пользователь проверяется по текущему паролю и активной сессии БД: права >=2 для просмотра (как TL), >=3 для модерации. У pending зелёная btn-success «Одобрить» и красная btn-danger «Отключить»; approved — только «Отключить»; disabled — нет кнопок.

Запросы к сайту по существующему VPN, HTTPS с проверкой сертификата домена, HMAC-SHA256 и одноразовым nonce. Секрет root:www-data 0640 в /etc/sanata/comfort-booking-moderation-token. Установить после Git через php tools/install-comfort-booking-moderation.php, ключ подать stdin по защищённому административному соединению. Тот же ключ создан на сайте его установщиком. Не выводить ключ в браузер/журнал/Git. Проверка: php tests/comfort-booking-moderation.php. Серверная часть совместима с PHP 7.1.
