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
    Schema::create('movie_categories', function (Blueprint $table) {
        // Movie ID foreign key
        $table->unsignedBigInteger('movie_id');
        $table->foreign('movie_id')
              ->references('movie_id')  // tham chiếu đến movie_id trong bảng movies
              ->on('movies')
              ->onDelete('cascade');

        // Category ID foreign key
        $table->unsignedBigInteger('category_id');
        $table->foreign('category_id')
              ->references('category_id')  // tham chiếu đến category_id trong bảng categories
              ->on('categories')
              ->onDelete('cascade');

        $table->primary(['movie_id', 'category_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_categories');
    }
};
