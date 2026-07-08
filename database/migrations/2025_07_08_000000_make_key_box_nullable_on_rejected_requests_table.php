<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rejected_requests', function (Blueprint $table) {
            // A request can be rejected/canceled while still pending, before a
            // physical key box has been assigned, so this must be optional.
            $table->string('key_box')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rejected_requests', function (Blueprint $table) {
            $table->string('key_box')->nullable(false)->change();
        });
    }
};
