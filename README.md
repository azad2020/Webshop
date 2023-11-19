# API Webshop Backend

This project implements a simplified mini webshop API using Laravel.

## Getting Started

To get this project up and running, follow these steps:

### Prerequisites

Make sure you have the following installed:

- [Composer](https://getcomposer.org/)
- [PHP](https://www.php.net/) (>= 8.2)
- [MySQL](https://www.mysql.com/) 

### Installation

Clone this repository to your local machine.

```bash
git clone <https://github.com/azad2020/Webshop>
cd Webshop-API

1. Install dependencies using Composer.
composer install

2. Set up the environment variables.
cp .env.example .env

3. Run database migrations and seeders.
php artisan migrate
php artisan db:seed

4. Start the development server.
php artisan serve

-----------------------------------------------------------------------------------------------------------------------------------------------------------------

API Endpoints

Import Master Data:
First add customers.csv and products.csv to app/public/ directory 
Then you can Use the following artisan command to import master data from provided CSV files.

php artisan import:masterdata

REST Service Endpoints
List all orders: GET /api/v1/orders
Get a specific order: GET /api/v1orders/{id}
Create a new order: POST /api/v1/orders
Update an order: PUT /api/v1/orders/{id}
Delete an order: DELETE /api/v1/orders/{id}

Add product to order: POST /api/v1/orders/{id}/add-product
Pay for an order: POST /api/v1/orders/{id}/pay

Additional Endpoints
List all customers: GET /api/v1/customers
Get a specific customer: GET /api/v1/customers/{id}
Create a new customer: POST /api/v1/customers
Update an customer: PUT /api/v1/customers/{id}
Delete an customer: DELETE /api/v1/customers/{id}

List all products: GET /api/v1/products
Get a specific product: GET /api/v1/products/{id}
Create a new product: POST /api/v1/products
Update an product: PUT /api/v1/products/{id}
Delete an product: DELETE /api/v1/products/{id}
-----------------------------------------------------------------------------------------------------------------------------------------------------------------
Testing
To test the functionality of these APIs, use tools like Postman.

-----------------------------------------------------------------------------------------------------------------------------------------------------------------
Micro Payment Provider

Payment Request Example

{
    "order_id": 23,
    "customer_email": "user@email.com",
    "value": 33.4
}


Payment Response Examples
Success:

{
    "message": "Payment Successful"
}

Failure:

{
    "message": "Insufficient Funds"
}


