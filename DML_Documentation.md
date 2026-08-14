# DML & Indexing Documentation

## 1. Overview
As part of the optimization strategy for the Majoo Reporting Engine, specific database indexing strategies were implemented to enhance the performance of the monthly revenue reports.

## 2. Table Indexing Strategy

### The Problem
The monthly report groups transaction data by `created_at` (specifically grouping by day) and filters by `merchant_id` and an optional `outlet_id`. 
The default primary key on the `transactions` table (`id`) is not sufficient for range queries and grouping by merchant/outlet within a specific date range. As the data grows, queries filtering by `merchant_id` and a specific month would require a full table scan, degrading performance.

### The Solution: Composite Indexes
We implemented the following composite indexes on the `transactions` table:

1. `idx_merchant_created` on `(merchant_id, created_at)`
2. `idx_merchant_outlet_created` on `(merchant_id, outlet_id, created_at)`

### Justification

#### 1. Composite Index: `(merchant_id, created_at)`
**Query Benefited:** 
```sql
SELECT TO_CHAR(created_at, 'YYYY-MM-DD'), SUM(bill_total), COUNT(id) 
FROM transactions 
WHERE merchant_id = ? AND created_at >= ? AND created_at <= ? 
GROUP BY TO_CHAR(created_at, 'YYYY-MM-DD');
```
**Why this works:** The report generation always requires filtering by `merchant_id` and a specific date range (the given month). By combining `merchant_id` and `created_at` in a single index, the database engine can immediately jump to the specific merchant's records and then sequentially scan only the records that fall within the requested month. This eliminates full table scans and drastically reduces disk I/O.

#### 2. Composite Index: `(merchant_id, outlet_id, created_at)`
**Query Benefited:**
```sql
SELECT TO_CHAR(created_at, 'YYYY-MM-DD'), SUM(bill_total), COUNT(id) 
FROM transactions 
WHERE merchant_id = ? AND outlet_id = ? AND created_at >= ? AND created_at <= ? 
GROUP BY TO_CHAR(created_at, 'YYYY-MM-DD');
```
**Why this works:** When the user filters the report by a specific `outlet_id`, this index is utilized. The cardinality flows from broad (`merchant_id`) to specific (`outlet_id`), ending with the range filter (`created_at`). This ensures the database engine optimally utilizes the B-Tree index to find the exact rows needed for the aggregation.

## 3. Data Manipulation Language (DML) 
For the actual generation of the report, the Laravel application uses the following conceptual DML strategy:

```sql
SELECT 
    TO_CHAR(created_at, 'YYYY-MM-DD') AS date,
    SUM(bill_total) AS total_revenue,
    COUNT(id) AS total_transactions
FROM 
    transactions
WHERE 
    merchant_id = 1 
    AND created_at >= '2026-08-01 00:00:00' 
    AND created_at <= '2026-08-31 23:59:59'
    -- AND outlet_id = 1 (if provided)
GROUP BY 
    TO_CHAR(created_at, 'YYYY-MM-DD')
```

### Application-Side Aggregation
Since the requirements mandate that **dates with no transactions must display 0 revenue**, running a pure SQL `GROUP BY` is insufficient, as it omits empty dates.
Therefore, the optimization strategy is twofold:
1. **SQL Layer**: Fetch only the aggregated data for dates that *have* transactions using the optimized indexes. This keeps data transfer from DB to PHP minimal.
2. **Application Layer (PHP)**: Generate an array of all dates for the requested month, and map the SQL results to this array. Any dates not present in the SQL result are filled with `0`. This is much faster and less complex than generating calendar tables or recursive CTEs inside the database.
