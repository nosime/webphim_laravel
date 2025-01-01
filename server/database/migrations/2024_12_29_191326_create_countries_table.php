<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('countries', function (Blueprint $table) {
        $table->id('country_id');
        $table->string('name', 100);
        $table->string('slug', 100)->unique();
        $table->string('code', 10)->nullable();
        $table->boolean('is_active')->default(true);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
