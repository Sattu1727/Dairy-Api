<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('Userid')->unique();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->text('bio')->nullable();
            $table->string('company')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
