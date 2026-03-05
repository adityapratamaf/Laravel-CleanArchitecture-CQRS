# Laravel Clean Architecture + CQRS Starter Template

Starter template Laravel 12 dengan:

* ✅ Clean Architecture (Domain / Application / Infrastructure / Presentation)
* ✅ CQRS (Command + Query + Handler) + DTO di semua flow
* ✅ Support **API** dan **Web Blade**
* ✅ Routes berada di `app/Presentation/Routes`
* ✅ Views berada di `app/Presentation/Views`
* ✅ Helpers reusable di `app/Support/Helpers` (Pagination, Crypto)
* ✅ Contoh module: **User** dan **Product** + migration + seeder

---

## Requirements

* PHP `^8.2`
* Composer
* Database (MySQL/PostgreSQL/SQLite)

---

## 1) Membuat Project dari Nol

### 1.1 Create Laravel project

```bash
composer create-project laravel/laravel Laravel-CleanArchitecture-CQRS-Starter-Template
cd Laravel-CleanArchitecture-CQRS-Starter-Template
```

### 1.2 Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

### 1.3 Configure database

Edit `.env`, contoh MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starter_template
DB_USERNAME=root
DB_PASSWORD=
```

---

## 2) Membuat Struktur Clean Architecture + CQRS

Jalankan perintah berikut untuk membuat folder layer & module:

```bash
mkdir -p app/{Domain,Application,Infrastructure,Presentation,Support}

mkdir -p app/Domain/User/{Entities,Contracts}
mkdir -p app/Domain/Product/{Entities,Contracts}

mkdir -p app/Application/Shared/Bus
mkdir -p app/Application/User/{DTOs,Commands,Queries}
mkdir -p app/Application/User/Commands/{CreateUser,UpdateUser,DeleteUser}
mkdir -p app/Application/User/Queries/{GetUserById,ListUsers}

mkdir -p app/Application/Product/{DTOs,Commands,Queries}
mkdir -p app/Application/Product/Commands/{CreateProduct,UpdateProduct,DeleteProduct}
mkdir -p app/Application/Product/Queries/{GetProductById,ListProducts}

mkdir -p app/Infrastructure/Persistence/Eloquent/{Models,Repositories}
mkdir -p app/Infrastructure/Providers

mkdir -p app/Presentation/Routes
mkdir -p app/Presentation/Views/{users,products}
mkdir -p app/Presentation/Http/{Controllers,Requests,Resources}
mkdir -p app/Presentation/Http/Controllers/{Api,Web}
mkdir -p app/Presentation/Http/Requests/{User,Product}

mkdir -p app/Support/Helpers
```

---

## 3) Routing & Views dari `app/Presentation`

Template ini tidak menggunakan `routes/web.php` dan `routes/api.php` default.
Sebagai gantinya:

* ✅ Routes: `app/Presentation/Routes/web.php` dan `app/Presentation/Routes/api.php`
* ✅ Views: `app/Presentation/Views/...`

### 3.1 Load routes dari `app/Presentation/Routes`

Edit file: `app/Providers/RouteServiceProvider.php`

Ubah method `boot()`:

```php
public function boot(): void
{
    $this->routes(function () {
        \Illuminate\Support\Facades\Route::middleware('api')
            ->prefix('api')
            ->group(app_path('Presentation/Routes/api.php'));

        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(app_path('Presentation/Routes/web.php'));
    });
}
```

### 3.2 Load views dari `app/Presentation/Views`

Buat provider:

```bash
php artisan make:provider PresentationServiceProvider
```

Isi `app/Infrastructure/Providers/PresentationServiceProvider.php`:

```php
<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class PresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addLocation(app_path('Presentation/Views'));
    }
}
```

Daftarkan di `config/app.php` (providers):

```php
App\Infrastructure\Providers\PresentationServiceProvider::class,
```

---

## 4) CQRS Bus

CQRS bus sederhana, scalable, mudah dipahami:

* `app/Application/Shared/Bus/CommandBus.php`
* `app/Application/Shared/Bus/QueryBus.php`

Convention:

* `SomeCommand` -> `SomeCommandHandler`
* `SomeQuery` -> `SomeQueryHandler`

---

## 5) Helpers (Tools reusable)

Letak:

* `app/Support/Helpers/Pagination.php` → meta pagination
* `app/Support/Helpers/Crypto.php` → encrypt/decrypt string

Digunakan berkali-kali tanpa overengineering.

---

## 6) Migrations + Seeders

### 6.1 Migration users & products

Buat migration (jika belum ada):

```bash
php artisan make:migration create_products_table
# users table biasanya sudah ada di Laravel default
```

### 6.2 Seeder users & products

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder ProductSeeder
```

Daftarkan di `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        ProductSeeder::class,
    ]);
}
```

Jalankan:

```bash
php artisan migrate
php artisan db:seed
```

---

## 7) Menjalankan Project

Clear cache + autoload:

```bash
composer dump-autoload
php artisan optimize:clear
```

Jalankan server:

```bash
php artisan serve
```

---

## 8) Endpoint yang tersedia

### 8.1 Web (Blade)

* Users:

  * `GET /users`
  * `GET /users/create`
  * `POST /users`
  * `GET /users/{id}`
  * `GET /users/{id}/edit`
  * `PUT /users/{id}`
  * `DELETE /users/{id}`

* Products:

  * `GET /products`
  * `GET /products/create`
  * `POST /products`
  * `GET /products/{id}`
  * `GET /products/{id}/edit`
  * `PUT /products/{id}`
  * `DELETE /products/{id}`

### 8.2 API (JSON)

Base prefix: `/api`

* Users:

  * `GET /api/users`
  * `POST /api/users`
  * `GET /api/users/{id}`
  * `PUT /api/users/{id}`
  * `DELETE /api/users/{id}`

* Products:

  * `GET /api/products`
  * `POST /api/products`
  * `GET /api/products/{id}`
  * `PUT /api/products/{id}`
  * `DELETE /api/products/{id}`

Contoh body JSON create product:

```json
{
  "name": "New Product",
  "sku": "SKU-NEW-001",
  "price": 150000,
  "stock": 5,
  "description": "test"
}
```

---

## 9) Cara Menambah Module Baru (Pattern cepat)

Checklist module baru `X`:

1. **Domain**

   * `app/Domain/X/Entities`
   * `app/Domain/X/Contracts`

2. **Application**

   * DTOs: `CreateXDTO`, `UpdateXDTO`, `XDTO`, `PagedXDTO`
   * Commands + Handlers: Create/Update/Delete
   * Queries + Handlers: GetById/List

3. **Infrastructure**

   * Eloquent model + repository implement

4. **Presentation**

   * Requests: Store/Update
   * Controllers: Api + Web
   * Routes: tambah di `app/Presentation/Routes/api.php` & `web.php`
   * Views: tambah di `app/Presentation/Views/x`

5. **Bindings**

   * bind repository interface → eloquent repository di `CQRSServiceProvider`

---

## 10) Publishing ke Packagist (Create Project)

Agar orang bisa install template ini dengan nama project custom:

```bash
composer create-project vendor/laravel-cleanarchitecture-cqrs-starter-template MyProjectName
```

### 10.1 composer.json minimal untuk template

Pastikan di root `composer.json`:

* `"type": "project"`
* `"name": "vendor/laravel-cleanarchitecture-cqrs-starter-template"`

Contoh:

```json
{
  "name": "vendor/laravel-cleanarchitecture-cqrs-starter-template",
  "type": "project",
  "description": "Laravel 12 Clean Architecture + CQRS starter template (API + Blade)",
  "license": "MIT",
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "scripts": {
    "post-create-project-cmd": [
      "@php artisan key:generate --ansi",
      "@php artisan optimize:clear --ansi"
    ]
  }
}
```

### 10.2 Steps publish

1. Push repository ke GitHub
2. Daftarkan repo ke Packagist
3. Buat tag release (misal `v1.0.0`)
4. Packagist akan auto update

---

## Notes

* CQRS di template ini **simple & scalable**: Command/Query selalu punya Handler dan DTO.
* Tidak menggunakan package CQRS eksternal agar mudah dipahami & minim dependency.
* Views & routes sengaja dipindah ke `app/Presentation` agar konsisten dengan Clean Architecture.