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
