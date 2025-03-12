# E-Commerce Symfony 6

## Description
This application is an e-commerce platform developed with Symfony 6, PHP 8, MySQL, HTML, CSS, and JavaScript. It allows users to create an account, browse different product categories (IT, fashion, etc.), and make online purchases. Email validation is managed via MailHog.

## Prerequisites
Before installing and running this project, make sure you have the following tools installed on your machine:

- [PHP 8+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [MySQL](https://www.mysql.com/downloads/)
- [Symfony CLI](https://symfony.com/download)
- [MailHog](https://github.com/mailhog/MailHog) (for email validation)

## Installation

1. **Clone the project**
   ```bash
   git clone git@github.com:ZidaneCode/e-commerce.git
   cd e-commerce
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure the environment**
   Copy the `.env` file to `.env.local` and modify the database connection settings if necessary:
   ```env
   DATABASE_URL="mysql://root@127.0.0.1:3306/ecommerce?serverVersion=mariadb-10.4.27&charset=utf8mb4"
   MAILER_DSN=smtp://localhost:1025
   ```

4. **Create the database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:diff
   php bin/console doctrine:migrations:migrate
   ```

5. **Install front-end dependencies**
   ```bash
   npm install
   npm run build
   ```

6. **Start MailHog (for email validation)**
   ```bash
   mailhog
   ```
   Access the MailHog interface at: [http://localhost:8025](http://localhost:8025)

7. **Start the Symfony server**
   ```bash
   symfony server:start
   ```

## Usage
- Access the application via [http://127.0.0.1:8000](http://127.0.0.1:8000)
- Sign up and validate your email address through MailHog
- Browse different product categories
- Add products to the cart and place an order

## Author
Zidane REDJDAL - Symfony 6 Developer

