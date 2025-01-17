<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('country');
            $table->string('city');
            $table->string('address');
            $table->string('postal_code')->nullable();
            $table->string('invite_code');
            $table->boolean('is_approved')->default(false);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('organizer_events')->nullable();
            $table->string('organizer_tickets')->nullable();
            $table->string('organizer_created_events')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
