<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_unique_id');
            $table->string('product_image');
            $table->enum('type', ['featured', 'gallery']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);

            // Foreign key constraint
            $table->foreign('product_unique_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_image');
    }
};
