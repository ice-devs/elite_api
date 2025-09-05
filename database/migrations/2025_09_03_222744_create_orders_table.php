<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderId')->unique();
            $table->string('clientName')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('name');
            $table->string('status');
            $table->integer('deliveryFee');
            $table->integer('subtotal');
            $table->integer('total');
            $table->integer('amountPaid');
            $table->integer('balance');
            $table->string('coupon')->nullable();
            $table->integer('discount')->nullable();
            $table->json('product'); // JSON is better if structured
            $table->string('deliveryState')->nullable();
            $table->string('deliveryMethod')->nullable();
            $table->string('payMethod')->nullable();
            $table->string('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
