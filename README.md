
## Учебный PHP + React проект 

Разрабатывался во время изучения PHP, в частности REST, Guzzle, Eloquent, Doctrine, PhpUnit.


## В ходе изучения реализовано
* Небольшой CRUD с валидацией
* Регистрация, авторизация (sessions, remember_token)
* CSRF-защита
* Собственный роутер с middleware (в Laravel-стиле)
* Внешний API через Guzzle (фан-факты и перевод)

## Запуск
Создайте **корневой** env:
```
cp .env.example .env
```
Создайте **backend**-env:
```
cp backend/.env.example backend/.env
```
Создайте **frontend**-env:
```
cp backend/.env.example frontend/.env
```
Запустите с помощью **docker compose**
```
docker compose up -d --build
```
Примените **Doctrine**-миграции
```
./vendor/bin/doctrine-migrations migrations migrate
``` 
## Технологии
| Frontend    | Backend             |Infrastructure |
| ------------|---------------------|---------------|
|React        | PHP 8.4+            |Docker Compose |
|TypeScript   | Illuminate Container|   Nginx       |
|React Router | Eloquent ORM        | PHP-FPM       |
|Vite         | Doctrine Migrations |               |
|Tailwind     |     MySQL           |               |

## Тестовая БД 
Integration-тесты используют отдельную базу данных. Её название должно заканчиваться на _test.
## API

### CSRF
```
GET /api/csrf
```
### Авторизация
```
POST /api/register
POST /api/login
GET  /api/me
POST /api/logout
```
### Cats
```
GET    /api/cats
POST   /api/cats
GET    /api/cats/{id}
PUT    /api/cats/{id}
PATCH  /api/cats/{id}
DELETE /api/cats/{id}
```
### Facts
```
GET /api/catfacts
GET /api/catfacts/{amount}
GET /api/catfacts/{amount}/{length}
```
## Пример запроса 
```
POST /api/cats
Content-Type: application/json
X-CSRF-Token: TOKEN
Cookie: PHPSESSID=sessionId

{
  "name": "Kisa",
  "lang": "meow"
}
```
