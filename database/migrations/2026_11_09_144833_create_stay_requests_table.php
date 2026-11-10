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
        Schema::create('stay_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->unsignedSmallInteger('guests');
            $table->date('check_in');
            $table->date('check_out');
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stay_requests');
    }
};
