<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->boolean('status')->default(1);
            $table->timestamps(); // This will create 'created_at' and 'updated_at' columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
