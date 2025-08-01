<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('start_date'); // Booking start date and time
            $table->dateTime('end_date'); // Booking end date and time
            $table->decimal('total_price', 10, 2); // Total price for the booking
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending'); // Booking status
            $table->text('notes')->nullable(); // Optional notes for the booking
            $table->string('payment_status')->default('unpaid'); // Payment status (e.g., unpaid, paid, refunded)
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
        Schema::dropIfExists('bookings');
    }
}
