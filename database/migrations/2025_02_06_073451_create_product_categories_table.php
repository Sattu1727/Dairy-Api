<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->timestamps(); // This will create `created_at` and `updated_at` columns
            $table->timestamp('deleted_at')->nullable(); // Soft delete column
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
