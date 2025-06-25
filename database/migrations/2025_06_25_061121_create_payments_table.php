<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('payment_method');
            $table->decimal('amount_paid', 10, 2);
            $table->timestamp('payment_date')->useCurrent();
            $table->enum('status', ['paid', 'failed', 'refunded'])->default('paid');
            $table->string('transaction_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};