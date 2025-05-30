### REST-api для онлайн магазина (Laravel)

(PHP PHP 8.0+, Laravel 12.x, MySQL)

#### Установка и запуск:

git clone https://github.com/hallernsk/laravel-ecom-api

cd laravel-ecom-api

cp  .env.example  .env  *(настроить DB_CONNECTION)*

php artisan key:generate

composer install

php artisan migrate --seed  (создаются 3 способа оплаты и 10 товаров)

php artisan serve

#### REST API:

Все методы API (кроме регистрации, входа, просмотра товаров) требуют токена в заголовке:  
Authorization: Bearer <token> (используется Sanctum)

Регистрация:  
POST api/register  
{  
    "name": "Test User",  
    "email": "test@example.com",  
    "password": "password123",  
    "password_confirmation": "password123"  
}  

Вход:  
POST api/login  
{  
    "email": "test@example.com",  
    "password": "password123"  
}   
В ответе приходит Bearer token для последующих запросов

ТОВАРЫ

Получить список товаров:  
GET api/products?sort_by_price=asc (сортировка по цене)

Просмотр товара:  
GET api/products/{product_id}

КОРЗИНА

Добавить товар в корзину:  
POST api/cart/add  
{  
    "product_id": 4,  
    "quantity": 2  
}  

Удалить товар из корзины:  
DELETE api/cart/remove  
{  
    "product_id": 4  
}  

Получить содержимое корзины:  
GET api/cart

ЗАКАЗЫ

Сформировать заказ (из корзины):  
POST api/checkout  
{  
    "payment_method_id": 1  
}  
В ответе ссылка на оплату - "payment_link" : "http://localhost:8000/pay/3/credit_card"

При переходе по этой ссылке (POST pay/{order_id}/{pay_method}) в ответе приходит ссылка на api url  
для обновления статуса заказа(На оплату -> Оплачен)  
 "confirm_api_url": "http://localhost:8000/api/orders/4/confirm-payment"   
(POST api/orders/{order_id}/confirm-payment)  

Получить список заказов:  
GET api/orders?status=Отменен&sort_by_date=desc (фильтр по статусу, сортировка по дате создания)

Получить заказ по id:  
GET api/orders/{id}

Фоновая задача: автоматическая отмена заказов.  
Логика отмены заказа реализована через command + Schedule  
Чтобы заказы старше 2 минут получали статус "Отменен", нужно также настроить cron (в Linux).  










