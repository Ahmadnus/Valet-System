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

        Schema::disableForeignKeyConstraints();
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->enum('status', ['pending', 'accepted', 'in_progress','delivering', 'preparing', 'completed', 'canceled'])->default('pending');
            $table->boolean('fee_waived')->default(false);
            $table->foreignId('valet_agent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->timestamp('in_progress_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
