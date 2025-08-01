<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Payment amount
            $table->string('payment_method'); // e.g., credit card, PayPal
            $table->string('transaction_id')->unique(); // Unique transaction identifier
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending'); // Payment status
            $table->text('notes')->nullable(); // Optional notes for the payment
            $table->dateTime('paid_at')->nullable(); // Date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
