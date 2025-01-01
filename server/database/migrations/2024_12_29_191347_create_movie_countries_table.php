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
        Schema::create('movie_countries', function (Blueprint $table) {
            // Movie ID foreign key
            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')
                  ->references('movie_id')  // tham chiếu đến movie_id trong bảng movies
                  ->on('movies')
                  ->onDelete('cascade');

            // Country ID foreign key
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')
                  ->references('country_id') // tham chiếu đến country_id trong bảng countries
                  ->on('countries')
                  ->onDelete('cascade');

            $table->primary(['movie_id', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_countries');
    }
};
