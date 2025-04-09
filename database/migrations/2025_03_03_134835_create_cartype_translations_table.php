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
        Schema::create('cartype_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cartype_id')->constrained()->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('type');
            $table->unique(['cartype_id', 'locale']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartype_translations');
    }
};
