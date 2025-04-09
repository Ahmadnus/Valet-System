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
    {    Schema::disableForeignKeyConstraints();
        Schema::create('rating_translations', function (Blueprint $table) {
            $table->id();

            $table->string('locale')->index();
            $table->string('comment');
            $table->foreignId('rating_id')->constrained('ratings')->onDelete('cascade');

    $table->unique(['rating_id', 'locale']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_translations');
    }
};
