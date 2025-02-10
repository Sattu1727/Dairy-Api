<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('status', ['in_use', 'not_in_use'])->default('in_use');
            $table->timestamp('start_date')->useCurrent();
            $table->integer('product_id')->unique();
            $table->timestamp('end_date')->nullable();
            $table->timestamps(); // created_at and updated_at
            $table->boolean('is_deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
