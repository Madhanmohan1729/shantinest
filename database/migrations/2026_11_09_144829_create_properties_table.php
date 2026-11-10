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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('property_type')->nullable();
            $table->string('address')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('max_guests')->default(2);
            $table->unsignedSmallInteger('bedrooms')->default(1);
            $table->unsignedSmallInteger('beds')->default(1);
            $table->unsignedSmallInteger('bathrooms')->default(1);
            $table->json('amenities')->nullable();
            $table->json('house_rules')->nullable();
            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('11:00');
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
