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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users'); // The user who is giving the rating
            $table->foreignId('ratee_id')->constrained('users'); // The user who is receiving the rating
            $table->string('rater_role'); // Role of the rater (who gives the rating)
            $table->string('ratee_role'); // Role of the ratee (who receives the rating)
            $table->integer('rating'); // Score (1-5)
            $table->text('comment')->nullable(); // Optional comment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
