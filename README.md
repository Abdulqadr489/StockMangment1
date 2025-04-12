# Laravel Stock Managment


## Installation

First clone this repository, install the dependencies, and setup .env file.

```
git clone https://github.com/Abdulqadr489/StockMangment1.git

composer install

copy .env.example .env

php artisan key:generate

php artisan passport:keys

 php artisan  migrate

php artisan passport:client --personal -> after enter ask (name the personal access client: depend your clinet name. EX:client)
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

here you can download the APIs test Post man (https://elements.getpostman.com/redirect?entityId=41011101-83a1602d-efc0-4546-9096-297824eb21da&entityType=collection)


## End-pints

Item Categories: Manage different categories of items in the system.

Branches: Create, update, and manage different branches.

Warehouse Stock: Manage stock specifically at the warehouse level. You can add new stock, update stock levels, and view the current warehouse stock.

Sales: Record and view sales transactions across different branches.

Stock: Manage warehouse and branch stock levels.

Transfers: Record stock movements between warehouse and branches.

Customers: Manage customer data and details.
