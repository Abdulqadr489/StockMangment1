# Laravel Stock Managment


## Installation

First clone this repository, install the dependencies, and setup .env file.

```
git clone https://github.com/Abdulqadr489/StockManagment.git

composer install

copy .env.example .env

php artisan key:generate

php artisan passport:keys

 php artisan  migrate

php artisan passport:client --personal
```



## Features

- register / login 
- Category management
- Item management
- Brunch management
- Customers management
- Record sales (with multiple items per sale and per brunch)
- Record transfer from wearhouse to stock (with multiple items)
- Retrieve sales per branch or all branches
- API secured with Laravel Passport


## APIs




