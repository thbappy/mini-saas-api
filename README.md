# Mini SaaS POS System - Quick Installation

## Prerequisites

Before installing, make sure you have the following installed:

- **PHP** 8.0 or higher
- **Composer** (PHP Package Manager)
- **MySQL** 5.7 or higher
- **Node.js** (Optional, for frontend)
- **Git** (Optional, for cloning)

---

## Installation Steps

### Step 1: Clone or Download Project

**Using Git:**
```bash
git clone https://github.com/thbappy/mini-saas-api.git
cd mini-saas-api
```

**Or download and extract ZIP file**

---

### Step 2: Install Dependencies

```bash
composer install
```

This command installs all PHP dependencies required by Laravel.

---

### Step 3: Create Environment File

```bash
cp .env.example .env
```

Or manually create a `.env` file in the root directory.

---

### Step 4: Configure Database

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_saas_pos
DB_USERNAME=root
DB_PASSWORD=0k
```

**Or create database manually:**

```bash
mysql -u root -p
CREATE DATABASE mini_saas_pos;
EXIT;
```

---

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

This creates a unique encryption key for your application.

---

### Step 6: Run Database Migrations

```bash
php artisan migrate:fresh --seed
```

This command:
- Creates all database tables
- Seeds sample data (optional)

---

### Step 7: Create Storage Link (Optional)

```bash
php artisan storage:link
```

---

### Step 8: Start Development Server

```bash
php artisan serve
```

The application will be available at: **http://localhost:8000**

---

## Verification

### Check if installation was successful:

1. Open your browser
2. Go to `http://localhost:8000`
3. You should see the API documentation or welcome page

### Test API:

```bash
curl -X GET http://localhost:8000/api/products
```

---

## First Time Setup

### 1. Register a New Shop

**Endpoint:** `POST /api/auth/register`

**Request:**
```json
{
  "tenant_name": "Your Shop Name",
  "tenant_slug": "your-shop-slug",
  "tenant_email": "shop@example.com",
  "name": "Your Name",
  "email": "your@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Using Curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_name": "Rohim Shop",
    "tenant_slug": "rohim-shop",
    "tenant_email": "rohim@shop.com",
    "name": "Mohammad Rohim",
    "email": "rohim@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

---

### 2. Login and Get Token

**Endpoint:** `POST /api/auth/login`

**Request:**
```json
{
  "email": "your@example.com",
  "password": "password123"
}
```

**Using Curl:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "rohim@gmail.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "message": "Login successful",
  "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz...",
  "user": {
    "id": 1,
    "name": "Mohammad Rohim",
    "email": "rohim@gmail.com",
    "tenant_id": 1
  }
}
```

**Save this token** - You'll need it for all API requests.

---

## Common Issues and Solutions

### Issue: "SQLSTATE[HY000]: General error: 1030"

**Solution:**
```bash
php artisan migrate:fresh --seed
```

---

### Issue: "Composer not found"

**Solution:** Install Composer from https://getcomposer.org

---

### Issue: "PHP version too low"

**Solution:** Upgrade PHP to version 8.0 or higher

---

### Issue: "Port 8000 already in use"

**Solution:**
```bash
php artisan serve --port=8001
```

---

### Issue: "Database connection error"

**Solution:**
1. Check if MySQL is running
2. Verify `.env` database credentials
3. Make sure database exists

---

## Project Structure

```
mini-saas-pos/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/      # API Controllers
│   │   ├── Requests/             # Input Validation
│   │   ├── Resources/            # API Response Format
│   │   └── Middleware/           # Middlewares
│   ├── Models/                   # Database Models
│   └── Policies/                 # Authorization Logic
├── database/
│   ├── migrations/               # Database Schema
│   └── seeders/                  # Sample Data
├── routes/
│   └── api.php                   # API Routes
├── config/
│   ├── app.php                   # App Config
│   └── database.php              # Database Config
├── .env                          # Environment Variables
├── composer.json                 # PHP Dependencies
└── README.md                     # Documentation
```

---

## Configuration

### Important Files to Check:

1. **`.env`** - Environment variables
2. **`config/app.php`** - Application settings
3. **`config/database.php`** - Database settings
4. **`routes/api.php`** - API endpoints

---

## Database Tables Created

- `tenants` - Shop/Business information
- `users` - User accounts
- `products` - Product inventory
- `customers` - Customer information
- `orders` - Sales orders
- `order_items` - Order line items
- `personal_access_tokens` - API tokens

---

## API Documentation

### Authentication Endpoints
```
POST   /api/auth/register       - Register new shop
POST   /api/auth/login          - Login user
POST   /api/auth/logout         - Logout user
GET    /api/auth/me             - Get current user
```

### Product Endpoints
```
GET    /api/products            - List all products
POST   /api/products            - Create product
GET    /api/products/{id}       - Get single product
PUT    /api/products/{id}       - Update product
DELETE /api/products/{id}       - Delete product
```

### Customer Endpoints
```
GET    /api/customers           - List all customers
POST   /api/customers           - Create customer
GET    /api/customers/{id}      - Get single customer
PUT    /api/customers/{id}      - Update customer
DELETE /api/customers/{id}      - Delete customer
```

### Order Endpoints
```
GET    /api/orders              - List all orders
POST   /api/orders              - Create order
GET    /api/orders/{id}         - Get single order
PUT    /api/orders/{id}         - Update order
DELETE /api/orders/{id}         - Delete order
POST   /api/orders/{id}/mark-as-paid    - Mark paid
POST   /api/orders/{id}/cancel          - Cancel order
```

### Report Endpoints
```
GET    /api/reports/daily-sales          - Daily sales report
GET    /api/reports/top-selling-products - Top products
GET    /api/reports/low-stock            - Low stock alert
```

---

## Testing with Postman

1. Download Postman from https://www.postman.com/downloads/
2. Import the Postman Collection (if provided)
3. Set environment variable `base_url` to `http://localhost:8000`
4. Set `token` variable with your API token
5. Start making API requests

---

## Next Steps

1. ✅ Installation complete
2. 📝 Create your first shop
3. 📦 Add products
4. 👥 Add customers
5. 🛒 Create orders
6. 📊 View reports

---

## Support

For issues or questions:
- Check the documentation
- Review error messages carefully
- Check `.env` file configuration
- Ensure database is running

---

## Version Information

- **Framework:** Laravel 9.x or 10.x
- **Database:** MySQL 5.7+
- **PHP:** 8.0+
- **API:** RESTful API with JSON responses

---

**Installation Complete!** 🎉

Start using the API at `http://localhost:8000`
