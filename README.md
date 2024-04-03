# Микросервис для работы с бонусным балансом пользователей
## Список используемых технологий:
* [Symfony](https://github.com/symfony/symfony) - PHP framework
* [Docker](https://www.docker.com/) - Docker
* [Postman](https://www.postman.com/) - тестирование API
* [Composer](https://getcomposer.org/download/) - пакетный менеджер PHP
## Для запуска приложения:
Для начала установите Docker с официального сайта - [ссылка](https://www.docker.com/)
Далее установите Composer - [ссылка](https://getcomposer.org/download/)
Теперь скопируйте проект

``` bash
git clone https://github.com/XsEon73/Loyalty.git
```

Теперь разверните проект Symfony
```  bash
composer install
```
Если при развертывании возникает ошибка
``` The zip extension and unzip/7z commands are both missing, skipping. ``` , то в php.ini раcкомментируйте строчку ```extension=zip```

Далее создайте контейнер с помощью docker-compose
```
docker-compose up
```
Теперь подключитесь к базе данных. По стандарту там стоит:<br />
* User - user <br />
* Password - user <br />
* Database - crm <br />

Далее произведите миграции Doctrine
```  bash
php bin/console doctrine:migrations:migrate
```
После выполнения предыдущих шагов можно запускать проект
```  bash
symfony serve
```
## Примеры запросов/ответов
**В основной директории проекта есть файл с названием Loyalty.postman_collection.json. Его можно импортировать в приложение Postman, чтобы получить доступ ко всем примерам запросов.**
### Создание компании
``` bash
POST http://127.0.0.1:8000/companies/create
# Body(json)
{
    "name": "Тест 1"
}
```
### Просмотр существующих компаний
``` bash
GET http://127.0.0.1:8000/companies
```
### Просмотр существующих балансов, которые привязаны к компании
``` bash
GET http://127.0.0.1:8000/companies/balances/1
```
### Создание баланса
``` bash
POST http://127.0.0.1:8000/balances/create
# Body(json)
{
    "phone" : "7909523235",
    "companyId" : "1",
    "balance" : 10000
}
```
### Просмотр существуюих балансов
``` bash
GET http://127.0.0.1:8000/balances/
```
### Просмотр существуюих балансов по номеру телефона
``` bash
GET http://127.0.0.1:8000/balances/{phone}
```
### Просмотр транзакций по балансу
``` bash
GET http://127.0.0.1:8000/balances/transaction
# Body(json)
{
    "phone" : "7909523235",
    "companyId" : 1
}
```
### Начисление/Списание с баланса по номеру телефона
``` bash
POST http://127.0.0.1:8000/balances/modification
# Body(json)
{
    "phone" : "7909523235",
    "companyId" : 1,
    "amount" : 10000
}
```
### Перевод средств от пользователя к пользователю
``` bash
POST http://127.0.0.1:8000/balances/send
# Body(json)
{
    "phone1" : "7909523235",
    "companyId1" : 1,
    "phone2" : "79058556231",
    "companyId2" : 1,
    "amount" : 10000
}
```
