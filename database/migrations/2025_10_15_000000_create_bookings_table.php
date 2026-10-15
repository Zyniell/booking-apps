<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->string('booking_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            $table->date('booking_date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->unsignedSmallInteger('slot_duration');
            $table->unsignedSmallInteger('slot_count');

            $table->decimal('base_price', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->json('meta')->nullable();
            $table->text('customer_note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
