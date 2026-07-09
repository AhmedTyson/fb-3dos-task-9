<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable FK checks temporarily (order_items FKs reference products but id lacks PK)
        DB::statement("PRAGMA foreign_keys = OFF");

        // 1. Backfill NULL ids with their SQLite rowids
        DB::statement("UPDATE products SET id = rowid WHERE id IS NULL");

        // 2. Recreate table with correct schema (id was INT not INTEGER PRIMARY KEY)
        DB::statement("
            CREATE TABLE products_new (
                id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                category_id INTEGER NOT NULL,
                name        TEXT    NOT NULL,
                description TEXT    NOT NULL DEFAULT '',
                size        TEXT,
                base_price  REAL    NOT NULL DEFAULT 0,
                stock       INTEGER NOT NULL DEFAULT 0,
                in_stock    INTEGER NOT NULL DEFAULT 0,
                images      TEXT,
                deleted_at  TEXT,
                created_at  TEXT,
                updated_at  TEXT,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            )
        ");

        // 3. Copy data
        DB::statement("
            INSERT INTO products_new (
                id, category_id, name, description, size, base_price,
                stock, in_stock, images, deleted_at, created_at, updated_at
            ) SELECT
                id, category_id, name, description, size, base_price,
                stock, in_stock, images, deleted_at, created_at, updated_at
            FROM products
        ");

        // 4. Create indexes on the new table before renaming
        DB::statement("CREATE INDEX idx_products_name ON products_new(name)");
        DB::statement("CREATE INDEX idx_products_category_id ON products_new(category_id)");
        DB::statement("CREATE INDEX idx_products_deleted_at ON products_new(deleted_at)");

        // 5. Swap tables
        DB::statement("DROP TABLE products");
        DB::statement("ALTER TABLE products_new RENAME TO products");

        // 6. Sync autoincrement sequence (after rename so new table's sequence is correct)
        $maxId = DB::table('products')->max('id') ?? 1;
        DB::statement("DELETE FROM sqlite_sequence WHERE name = 'products'");
        DB::statement("INSERT INTO sqlite_sequence (name, seq) VALUES ('products', ?)", [$maxId]);

        // Re-enable foreign keys (new table has proper PK so FKs will work)
        DB::statement("PRAGMA foreign_keys = ON");
    }

    public function down(): void
    {
        DB::statement("PRAGMA foreign_keys = OFF");

        // Restore old schema (id INT without primary key)
        DB::statement("
            CREATE TABLE products_old (
                id          INT,
                category_id INT,
                name        TEXT,
                description TEXT,
                size        TEXT,
                base_price  NUM,
                stock       INT,
                in_stock    INT,
                images      TEXT,
                deleted_at  NUM,
                created_at  NUM,
                updated_at  NUM
            )
        ");

        DB::statement("
            INSERT INTO products_old SELECT * FROM products
        ");

        DB::statement("DROP TABLE products");
        DB::statement("ALTER TABLE products_old RENAME TO products");
    }
};
