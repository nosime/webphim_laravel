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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id('episode_id');

            // Foreign keys
            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')
                  ->references('movie_id')
                  ->on('movies')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('server_id');
            $table->foreign('server_id')
                  ->references('server_id')
                  ->on('servers');

            // Các cột khác
            $table->string('name');
            $table->string('slug');
            $table->string('file_name', 500)->nullable();
            $table->integer('episode_number');
            $table->integer('duration')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('embed_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('views')->default(0);
            $table->bigInteger('size')->nullable();
            $table->string('quality', 20)->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
