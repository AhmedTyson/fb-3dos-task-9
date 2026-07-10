<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Create new table with correct schema using Schema builder
        Schema::create('products_new', function (Blueprint $table) {
            $table->id(); // INTEGER PRIMARY KEY AUTOINCREMENT
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name'); // index added via $table->index('name') below
            $table->text('description');
            $table->string('size', 50);
            $table->decimal('base_price', 10, 2)->unsigned()->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('in_stock')->default(false);
            $table->json('images')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('name');
            $table->index('category_id');
            $table->index('deleted_at');
        });

        // 3. Copy data from old to new table
        $columns = [
            'id', 'category_id', 'name', 'description', 'size', 'base_price',
            'stock', 'in_stock', 'images', 'deleted_at', 'created_at', 'updated_at'
        ];

        DB::table('products_new')->insert(
            DB::table('products')->select($columns)->get()->toArray()
        );

        // 5. Swap tables
        Schema::drop('products');
        Schema::rename('products_new', 'products');

        // 6. Sync autoincrement sequence (SQLite only)
        if (DB::getDriverName() === 'sqlite') {
            $maxId = DB::table('products')->max('id') ?? 1;
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'products'");
            DB::statement("INSERT INTO sqlite_sequence (name, seq) VALUES ('products', ?)", [$maxId]);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Restore old schema (id INT without primary key)
        Schema::create('products_old', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('category_id');
            $table->string('name');
            $table->text('description');
            $table->string('size', 50);
            $table->decimal('base_price', 10, 2);
            $table->integer('stock');
            $table->boolean('in_stock');
            $table->json('images')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('INSERT INTO products_old SELECT * FROM products');

        Schema::drop('products');
        Schema::rename('products_old', 'products');

        Schema::enableForeignKeyConstraints();
    }
};