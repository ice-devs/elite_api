<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Database;
use PDOException;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan db:setup
     */
    protected $signature = 'db:setup';

    /**
     * The console command description.
     */
    protected $description = 'Create required database tables manually if they do not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $db = (new Database())->connect();

        $query_orders = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            orderId VARCHAR(255) NOT NULL UNIQUE,
            clientName VARCHAR(255) DEFAULT '',
            email VARCHAR(255) DEFAULT '',
            phone VARCHAR(50) DEFAULT '',
            address TEXT DEFAULT '',
            name VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL,
            deliveryFee INT NOT NULL,
            subtotal INT NOT NULL,
            total INT NOT NULL,
            amountPaid INT NOT NULL,
            balance INT NOT NULL,
            coupon VARCHAR(255),
            discount INT,
            product VARCHAR(10000),
            deliveryState VARCHAR(50),
            deliveryMethod VARCHAR(50),
            payMethod VARCHAR(50),
            date VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";

        $query_products = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            productId VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price INT NOT NULL,
            quantity INT NOT NULL,
            status VARCHAR(50) DEFAULT 'Active',
            salesCount INT DEFAULT 0,
            category VARCHAR(255) NOT NULL,
            image1 TEXT,
            image2 TEXT,
            image3 TEXT,
            image4 TEXT,
            createdAt VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";

        $query_delivery = "
        CREATE TABLE IF NOT EXISTS delivery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            state VARCHAR(255) NOT NULL,
            amount INT NOT NULL,
            createdAt VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";

        $query_categories = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            categoryId VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            createdAt VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";

        $query_coupons = "
        CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(255) NOT NULL,
            amount INT NOT NULL,
            createdAt VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";

        try {
            $db->exec($query_orders);
            $this->info("Table 'orders' created successfully or already exists.");

            $db->exec($query_products);
            $this->info("Table 'products' created successfully or already exists.");

            $db->exec($query_delivery);
            $this->info("Table 'delivery' created successfully or already exists.");

            $db->exec($query_categories);
            $this->info("Table 'categories' created successfully or already exists.");

            $db->exec($query_coupons);
            $this->info("Table 'coupons' created successfully or already exists.");
        } catch (PDOException $e) {
            $this->error("Error creating tables: " . $e->getMessage());
        }

        return 0;
    }
}
