<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->uuid('product_id')->notNull(); // UUID for product_id
            $table->string('stock_id')->notNull(); // Stock identifier
            $table->integer('quantity_in')->default(0)->notNull(); // Incoming stock
            $table->integer('quantity_out')->default(0)->notNull(); // Outgoing stock
            $table->enum('stock_status', ['low_stock', 'full_stock', 'out_of_stock', 'reserved_stock', 'damaged_stock', 'in_transit'])->notNull();
            $table->integer('stock_threshold')->default(10); // Threshold for low stock alerts
            $table->timestamp('last_sold_at')->nullable(); // Timestamp of last sale
            $table->string('batch_number')->nullable(); // Batch number if applicable
            $table->enum('status', ['Active', 'Inactive'])->default('Active'); // Stock status
            $table->timestamps(); // Created_at & Updated_at timestamps
            $table->boolean('is_deleted')->default(false); // Soft delete flag
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
