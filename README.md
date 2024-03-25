# Karma8 Test Task - Alexander Mavrin

_Сервис для рассылки уведомлений об истекающих подписках._

_За один и за три дня до истечения срока подписки, нужно отправить
письмо пользователю с текстом "{username}, your subscription is expiring
soon"._

Было бы гораздо проще сделать на golang, но поскольку я претендую на PHP разраба и в задании сказано, что не надо ничего лишнего, решил делать на чистом PHP + MySQL

За основу взял и немного модифицировал данный шаблон: https://github.com/nanoninja/docker-nginx-php-mysql, чтобы не возиться с окружением.

## Общая идея
Один скрипт (`web/app/check-emails.php`) будет проверять email юзеров, которым в ближайшие несколько часов нужно будет слать уведомление, и у которых статус емейла неясен. Поскольку эта процедура дорогая и долгая, будем запускать ее только для тех, для кого это действительно понадобится в ближайшее время.

Второй скрипт (`web/app/send-notifications.php`) будет рассылать уведомления юзерам с подтверждённым или проверенным емейлом.

Оба скрипта могут работать и работают в несколько потоков, запускаются в `web/entrypoint.sh`

Все функции работы с БД и прочие вынесены в `web/app/common.php`, в основных скриптах только логика.

## Установка и запуск
Для запуска необходимо клонировать репозиторий и поднять контейнеры:
```sh
make docker-start
```
Затем инициализировать БД (создать таблицы) и заполнить их тестовыми юзерами.
Команда просто загружает файл `sql/db.sql` в mysql:
```sh
make mysql-init
```
Многопоточный запуск скриптов проверки и отправки емейлов (запускает `web/app/entrypoint.sh`):
```sh
make run-scripts
```
Лог работы будет выводиться в stdout

---

Чтобы остановить и очистить всё, выполните команду:
```sh
sudo make docker-stop
```

## Просмотр результатов:
1. Следить за выводом команды `make run-scripts`
2. http://localhost:8000/ - здесь можно видеть статистику в самом общем виде (для простоты реализации страница обновляется вручную)
3. http://localhost:8080/ - phpMyAdmin, чтобы посмотреть что происходит в базе (доступ: dev:dev, база test)


