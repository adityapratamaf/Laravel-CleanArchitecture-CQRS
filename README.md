````md
# Laravel Clean Architecture + CQRS Starter Template

Starter template Laravel 12 yang menggunakan **Clean Architecture + CQRS** dengan struktur yang sederhana, scalable, rapi, dan tidak over-engineered.

Template ini dirancang agar bisa digunakan untuk:

* REST API
* Web UI (Blade)
* Proyek kecil sampai medium
* Starter untuk microservice atau modular monolith

---

## ✨ Features

* ✅ Laravel 12
* ✅ Clean Architecture
* ✅ CQRS Pattern
* ✅ DTO-based data transfer
* ✅ CommandBus & QueryBus
* ✅ Global API Exception Handler
* ✅ Support **API** dan **Web Blade**
* ✅ Laravel Sanctum Authentication
* ✅ Custom Pagination Helper
* ✅ Custom Crypto Helper
* ✅ Modular folder structure
* ✅ Routes berada di `app/Presentation/Routes`
* ✅ Views berada di `app/Presentation/Views`
* ✅ Contoh module: **User** dan **Product** + migration + seeder
* ✅ Ready for Packagist template usage

---

## 📋 Requirements

* PHP `^8.2`
* Composer
* Database (MySQL/PostgreSQL/SQLite)

---

## 🧠 Architecture Overview

Project ini menggunakan pendekatan **Clean Architecture** dengan pembagian layer berikut:

```text
app
 ├── Domain
 │
 ├── Application
 │   ├── Shared
 │   │   └── Bus
 │   ├── User
 │   │   ├── DTOs
 │   │   ├── Commands
 │   │   └── Queries
 │   └── Product
 │       ├── DTOs
 │       ├── Commands
 │       └── Queries
 │
 ├── Infrastructure
 │   ├── Persistence
 │   │   ├── Eloquent
 │   │   │   ├── Models
 │   │   │   └── Repositories
 │   └── Providers
 │
 ├── Presentation
 │   ├── Http
 │   │   ├── Controllers
 │   │   │   ├── Api
 │   │   │   └── Web
 │   │   ├── Requests
 │   │   └── Resources
 │   ├── Routes
 │   │   ├── api.php
 │   │   └── web.php
 │   └── Views
 │
 └── Support
     └── Helpers
````

---

## 🚀 1) Membuat Project dari Nol

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

## 🧱 2) Membuat Struktur Clean Architecture + CQRS

Jalankan perintah berikut untuk membuat folder layer dan module:

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

## 🛣️ 3) Routing & Views dari `app/Presentation`

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

Daftarkan di `config/app.php` pada bagian `providers`:

```php
App\Infrastructure\Providers\PresentationServiceProvider::class,
```

---

## 🚌 4) CQRS Pattern

Project ini menggunakan **Command Query Responsibility Segregation (CQRS)**.

### 4.1 Command

Command digunakan untuk **operasi yang mengubah data**.

Contoh:

```text
CreateUserCommand
UpdateUserCommand
DeleteUserCommand
CreateProductCommand
UpdateProductCommand
DeleteProductCommand
```

Setiap command akan diproses oleh handler terkait, misalnya:

```text
CreateUserCommandHandler
CreateProductCommandHandler
```

### 4.2 Query

Query digunakan untuk **operasi membaca data**.

Contoh:

```text
ListUsersQuery
GetUserByIdQuery
ListProductsQuery
GetProductByIdQuery
```

Setiap query akan diproses oleh handler terkait, misalnya:

```text
ListUsersQueryHandler
GetProductByIdQueryHandler
```

### 4.3 Convention

Convention yang dipakai:

* `SomeCommand` -> `SomeCommandHandler`
* `SomeQuery` -> `SomeQueryHandler`

---

## 📦 5) DTO (Data Transfer Object)

DTO digunakan untuk mentransfer data antar layer tanpa bergantung langsung pada request atau model.

Contoh:

```text
CreateUserDTO
UpdateUserDTO
CreateProductDTO
UpdateProductDTO
```

DTO membantu menjaga layer **Application tetap bersih dari framework dependency**.

---

## 🔄 6) Command Bus & Query Bus

Command dan Query tidak dipanggil langsung.

Controller akan menggunakan:

* `app/Application/Shared/Bus/CommandBus.php`
* `app/Application/Shared/Bus/QueryBus.php`

Contoh penggunaan:

```php
$commandBus->dispatch(new CreateUserCommand($dto));

$queryBus->ask(new ListUsersQuery());
```

CQRS bus pada template ini dibuat sederhana, scalable, dan mudah dipahami.

---

## 🧰 7) Helpers (Tools reusable)

Letak helper:

* `app/Support/Helpers/Pagination.php` → meta pagination
* `app/Support/Helpers/Crypto.php` → encrypt/decrypt string

Helper ini dipakai berulang kali tanpa overengineering.

Contoh helper lain yang bisa dikembangkan:

```text
PaginationLinks
CryptoHelper
ApiResponse
```

---

## 🛡️ 8) API Error Handling

Project ini menggunakan **Global Exception Handler** untuk API.

Semua error API akan otomatis diformat menjadi struktur yang konsisten.

Contoh server error:

```json
{
  "result": false,
  "code": 500,
  "message": "server error",
  "errors": null
}
```

Contoh validation error:

```json
{
  "result": false,
  "code": 422,
  "message": "validation error",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

---

## 🌐 9) Kenapa API beda dengan Web?

### 9.1 Web

Di **Web UI**, controller biasanya menggunakan `try-catch`.

Tujuannya:

* redirect kembali ke halaman sebelumnya
* mengirim flash message `session('error')`
* menampilkan alert di Blade

Contoh:

```php
try {
   $commandBus->dispatch(new CreateProductCommand($dto));
   return redirect('/products')->with('success', 'Product created');
} catch (DomainException $e) {
   return back()->withInput()->with('error', $e->getMessage());
}
```

Karena web UI perlu memberikan **feedback visual ke user**.

### 9.2 API

Pada API, exception **tidak perlu ditangkap di controller**.

Jika semua exception ditangkap di controller, maka akan menyebabkan:

* code menjadi repetitif
* banyak boilerplate
* response error tidak konsisten

Sebagai gantinya, project ini menggunakan **Global Exception Handler**.

Controller API cukup fokus pada:

* menerima request
* membuat DTO
* menjalankan command/query
* mengembalikan response sukses

Contoh:

```php
$p = $commandBus->dispatch(new CreateProductCommand($dto));

return response()->json([
    'id' => $p->id,
    'name' => $p->name
]);
```

Jika terjadi error, global handler akan otomatis menangani response JSON.

---

## 🗃️ 10) Migrations + Seeders

### 10.1 Migration users & products

Buat migration jika belum ada:

```bash
php artisan make:migration create_products_table
# users table biasanya sudah ada di Laravel default
```

### 10.2 Seeder users & products

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

Jalankan migration dan seeder:

```bash
php artisan migrate
php artisan db:seed
```

Atau sekaligus fresh + seed:

```bash
php artisan migrate:fresh --seed
```

---

## ▶️ 11) Menjalankan Project

Clear cache dan autoload:

```bash
composer dump-autoload
php artisan optimize:clear
```

Jalankan server:

```bash
php artisan serve
```

---

## 🔐 12) Authentication

API authentication menggunakan **Laravel Sanctum**.

### Login endpoint

```http
POST /api/auth/login
```

Response contoh:

```json
{
  "token_type": "Bearer",
  "token": "xxxxxx"
}
```

Gunakan token pada header:

```http
Authorization: Bearer TOKEN
```

---

## 🌐 13) Endpoint yang tersedia

### 13.1 Web (Blade)

#### 👤 Users

* `GET /users`
* `GET /users/create`
* `POST /users`
* `GET /users/{id}`
* `GET /users/{id}/edit`
* `PUT /users/{id}`
* `DELETE /users/{id}`

#### 📦 Products

* `GET /products`
* `GET /products/create`
* `POST /products`
* `GET /products/{id}`
* `GET /products/{id}/edit`
* `PUT /products/{id}`
* `DELETE /products/{id}`

#### 🔑 Auth

* `GET /login`
* `POST /logout`

### 13.2 API (JSON)

Base prefix: `/api`

#### 👤 Users

> Dokumentasi Users API tetap dipertahankan dan tidak dihapus.

* `GET /api/users`
* `POST /api/users`
* `GET /api/users/{id}`
* `PUT /api/users/{id}`
* `DELETE /api/users/{id}`

#### 📦 Products

* `GET /api/products`
* `POST /api/products`
* `GET /api/products/{id}`
* `PUT /api/products/{id}`
* `DELETE /api/products/{id}`

#### 🔐 Auth

* `POST /api/auth/login`
* `POST /api/auth/logout`

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

## 📘 14) Dokumentasi API Auth + Products (Sanctum)

Base URL:

```txt
http://localhost:8000
```

Kalau kamu pakai domain lain, tinggal ganti.

> **Catatan:** Dokumentasi **Users API** di atas tetap berlaku dan tidak dihapus.
> Bagian ini adalah tambahan dokumentasi untuk **Auth** dan **Products** dengan Bearer Token.

### 14.1 Login (ambil token)

#### Endpoint

```http
POST /api/auth/login
```

#### cURL

```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123",
    "device_name": "postman"
  }'
```

#### Response contoh

```json
{
  "token_type": "Bearer",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

Simpan nilai token untuk dipakai di request berikutnya.

---

### 14.2 Products (pakai Bearer Token)

Bagian ini berlaku jika route `products` dimasukkan ke group middleware `auth:sanctum`.

#### 📄 14.2.1 List products

##### Endpoint

```http
GET /api/products
```

##### cURL

```bash
curl -X GET "http://localhost:8000/api/products" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

---

#### ➕ 14.2.2 Create product

##### Endpoint

```http
POST /api/products
```

##### cURL

```bash
curl -X POST "http://localhost:8000/api/products" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -d '{
    "name": "New Product",
    "sku": "SKU-N-999",
    "price": 150000,
    "stock": 5,
    "description": "test product"
  }'
```

---

#### 🔍 14.2.3 Show product

##### Endpoint

```http
GET /api/products/{id}
```

##### cURL

```bash
curl -X GET "http://localhost:8000/api/products/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

---

#### ✏️ 14.2.4 Update product

##### Endpoint

```http
PUT /api/products/{id}
```

##### cURL

```bash
curl -X PUT "http://localhost:8000/api/products/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -d '{
    "name": "Product A Updated",
    "sku": "SKU-A-001",
    "price": 175000,
    "stock": 30,
    "description": "updated"
  }'
```

---

#### 🗑️ 14.2.5 Delete product

##### Endpoint

```http
DELETE /api/products/{id}
```

##### cURL

```bash
curl -X DELETE "http://localhost:8000/api/products/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

---

### 14.3 Logout (revoke token yang sedang dipakai)

#### Endpoint

```http
POST /api/auth/logout
```

#### cURL

```bash
curl -X POST "http://localhost:8000/api/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

#### Response contoh

```json
{ "message": "Logged out" }
```

---

## 🧩 15) Cara Menambah Module Baru (Pattern cepat)

Checklist module baru `X`:

### 15.1 Domain

* `app/Domain/X/Entities`
* `app/Domain/X/Contracts`

### 15.2 Application

* DTOs: `CreateXDTO`, `UpdateXDTO`, `XDTO`, `PagedXDTO`
* Commands + Handlers: Create/Update/Delete
* Queries + Handlers: GetById/List

### 15.3 Infrastructure

* Eloquent model + repository implementation

### 15.4 Presentation

* Requests: Store/Update
* Controllers: Api + Web
* Routes: tambah di `app/Presentation/Routes/api.php` dan `web.php`
* Views: tambah di `app/Presentation/Views/x`

### 15.5 Bindings

* bind repository interface → eloquent repository di `CQRSServiceProvider`

---

## 📦 16) Installation

Clone project:

```bash
git clone https://github.com/your-repo/Laravel-CleanArchitecture-CQRS-Starter-Template.git
```

Install dependency:

```bash
composer install
```

Copy env:

```bash
cp .env.example .env
```

Generate key:

```bash
php artisan key:generate
```

Run migration dan seed:

```bash
php artisan migrate --seed
```

Run server:

```bash
php artisan serve
```

---

````md
## 🧪 17) Menjalankan Unit Test

Project ini sudah siap untuk pengujian menggunakan **PHPUnit / Pest** bawaan Laravel.

### 17.1 Jalankan semua test

```bash
php artisan test
````

atau:

```bash
vendor/bin/phpunit
```

---

### 17.2 Jalankan test tertentu

Contoh menjalankan file test tertentu:

```bash
php artisan test tests/Feature/ProductTest.php
```

Atau berdasarkan nama test:

```bash
php artisan test --filter=ProductTest
```

---

### 17.3 Jalankan test dengan environment testing

Pastikan file `.env.testing` sudah disiapkan.

Contoh:

```env
APP_ENV=testing
APP_KEY=base64:your-app-key
APP_DEBUG=true

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_laravel_ca_cqrs_test
DB_USERNAME=${DB_SECRET_USERNAME}
DB_PASSWORD=${DB_SECRET_PASSWORD}
```

Lalu jalankan:

```bash
php artisan test
```

Laravel akan otomatis memakai environment testing saat test dijalankan.

---

### 17.4 Jalankan test setelah migrate fresh

Jika test membutuhkan database yang bersih, gunakan trait seperti:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
```

Contoh:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
    }
}
```

Trait `RefreshDatabase` akan membantu me-refresh database untuk setiap test agar data tetap konsisten.

---

### 17.5 Menjalankan test dengan coverage

Jika environment PHP kamu sudah mendukung coverage (`Xdebug` atau `PCOV`), jalankan:

```bash
php artisan test --coverage
```

Atau:

```bash
vendor/bin/phpunit --coverage-text
```

---

### 17.6 Rekomendasi struktur test

Untuk menjaga konsistensi dengan Clean Architecture + CQRS, pengujian bisa dipisah menjadi:

* `tests/Unit` → untuk test DTO, helper, service, handler, dan logic kecil
* `tests/Feature` → untuk test endpoint API, controller, auth, dan integrasi flow

Contoh:

```text
tests
 ├── Unit
 │   ├── Helpers
 │   ├── DTOs
 │   └── Handlers
 └── Feature
     ├── Auth
     ├── User
     └── Product
```

---

### 17.7 Contoh alur testing yang disarankan

Urutan yang umum dipakai saat development:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan test
```

Kalau ingin memastikan database testing benar-benar bersih:

```bash
php artisan config:clear
php artisan test
```

---

## 📦 18) Publishing ke Packagist (Create Project)

Agar orang bisa install template ini dengan nama project custom:

```bash
composer create-project vendor/laravel-cleanarchitecture-cqrs-starter-template MyProjectName
```

### 18.1 composer.json minimal untuk template

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

### 18.2 Steps publish

1. Push repository ke GitHub
2. Daftarkan repository ke Packagist
3. Buat tag release, misalnya `v1.0.0`
4. Packagist akan auto update

---

## 📝 19) Notes

* CQRS di template ini **simple & scalable**: Command/Query selalu punya Handler dan DTO.
* Tidak menggunakan package CQRS eksternal agar mudah dipahami dan minim dependency.
* Views & routes sengaja dipindah ke `app/Presentation` agar konsisten dengan Clean Architecture.
* Dokumentasi API **Users**, **Auth Login/Logout**, dan **Products** bisa dipakai sebagai dasar testing di Postman atau cURL.
* Template ini cocok dipakai sebagai base project maupun starter template open source.

---

## 📄 20) License

MIT License

```
MIT License © 2026 Aditya Pratama Febriono This project is open-sourced software licensed under the MIT license.
```
