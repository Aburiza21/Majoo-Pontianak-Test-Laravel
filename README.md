# Majoo Test - Laravel Reporting Engine

This project is a technical test implementation for Majoo, building a Reporting Engine API with strict multi-tenancy, JWT authentication, and pagination using the Laravel framework.

## 🚀 Features

- **Authentication**: Secured login using JWT (JSON Web Tokens).
- **Reporting Engine**: Generates a monthly revenue report for Merchants and Outlets.
  - Automatically handles days with no transactions by filling them with "0" revenue.
  - Implements Pagination (`LengthAwarePaginator`) for easy frontend consumption.
- **Strict Multi-Tenancy**: Data security is ensured by verifying the ownership of `merchant_id` and `outlet_id` before querying the transactions.
- **High Performance**: Employs an optimized DB indexing strategy and Redis caching to prevent database overload during complex reporting aggregations.

---

## 🛠️ Installation & Setup

1. **Clone the repository and install dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```
   Ensure your `.env` contains the proper configurations:
   ```env
   DB_CONNECTION=pgsql # Or mysql, sqlite
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=

   # For Performance Cache
   CACHE_STORE=redis
   ```

3. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

4. **Run Database Migrations & Seeding**
   ```bash
   php artisan migrate --seed
   ```
   *Note: Ensure you have your SQL service running before migrating.*

5. **Run the Application**
   ```bash
   php artisan serve
   ```

6. **Run Tests**
   ```bash
   php artisan test
   ```
   *The tests are fully isolated and use SQLite (`RefreshDatabase`) for speed. The test suite covers:*
   - *Feature Tests for Authentication (validations, empty fields, non-existent users).*
   - *Feature Tests for the Reporting Engine (pagination, missing parameters, edge cases like months with zero transactions).*
   - *Unit Tests for Eloquent Model Relationships.*
   - *Mocking of the Redis Cache Facade to ensure the caching layer is utilized properly.*

---

## 🏗️ System Architecture & Multi-Tenancy Security

This system implements a strict Multi-Tenancy architecture at the application level.

1. **User Request**: A request is received with a JWT token indicating the logged-in User.
2. **Access Control**: Before the report is generated, the `ReportController` ensures that the requested `merchant_id` belongs directly to the authenticated `User`.
   ```php
   Merchant::where('id', $merchantId)->where('user_id', $user->id)->first();
   ```
   If an `outlet_id` is passed, the system also verifies that the outlet belongs to that specific Merchant. Any violation returns a `403 Forbidden` response.
3. **Data Retrieval**: After verifying ownership, the system queries the `transactions` table.
4. **Caching Layer**: Aggregated reports are cached for 60 minutes in Redis with a composite key based on the `merchant_id`, `outlet_id`, and `month`. Subsequent requests will hit the cache instead of the DB, massively improving response times and reducing load.

---

## ⚡ Optimization & DML Documentation (Table Indexing Strategies)

To ensure the monthly reporting engine operates at high performance (specifically addressing the constraints of analyzing potentially millions of rows per month), the database schema implements specific indexing strategies.

### The Problem
Generating a monthly report requires aggregating data from the `Transactions` table using the following `WHERE` clauses:
1. `WHERE merchant_id = ?`
2. `WHERE created_at BETWEEN ? AND ?` (Using `whereYear` and `whereMonth`)
3. `WHERE outlet_id = ?` (Optional)

Without an index, the database engine would have to perform a **Full Table Scan**, which is extremely slow on large datasets.

### The Solution: Composite Indexes
In the migration file `2026_08_14_183956_add_indexes_to_transactions_table.php`, two composite indexes were implemented:

```php
Schema::table('transactions', function (Blueprint $table) {
    // 1. Index for Merchant-Level Reports
    $table->index(['merchant_id', 'created_at'], 'idx_merchant_created');
    
    // 2. Index for Outlet-Level Reports
    $table->index(['merchant_id', 'outlet_id', 'created_at'], 'idx_merchant_outlet_created');
});
```

### Index Justification
1. **`idx_merchant_created`**: When a user requests a report for a specific merchant over a specific month, the database can use this index to immediately jump to the subset of rows for that `merchant_id`, and then quickly filter the range of dates using the `created_at` part of the index.
2. **`idx_merchant_outlet_created`**: When the user requests a report and specifies an `outlet_id`, this index is utilized. Because the B-Tree index structure matches the exact order of the columns queried (`merchant_id` -> `outlet_id` -> `created_at`), the database avoids scanning the entire merchant's data and exclusively scans the targeted outlet's data for the given time frame.
3. **Index-Only Scans**: These indexes ensure that grouping by date and summing the `bill_total` is executed on an already sorted and filtered subset of records, improving report generation speeds from seconds to milliseconds.
