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
        Schema::create('movie_ratings', function (Blueprint $table) {
            $table->id('rating_id');

            // Foreign keys
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')
                  ->references('movie_id')
                  ->on('movies')
                  ->onDelete('cascade');

            // Các cột khác
            $table->integer('score')->nullable();
            $table->enum('rating_type', ['like', 'dislike', 'awesome']);
            $table->string('comment', 1000)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'movie_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_ratings');
    }
};
