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
        Schema::create('view_history', function (Blueprint $table) {
            $table->id('history_id');

            // Foreign keys
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')
                  ->references('movie_id')
                  ->on('movies');

            $table->unsignedBigInteger('episode_id');
            $table->foreign('episode_id')
                  ->references('episode_id')
                  ->on('episodes');

            $table->unsignedBigInteger('server_id');
            $table->foreign('server_id')
                  ->references('server_id')
                  ->on('servers');

            // Các cột khác
            $table->timestamp('view_date')->useCurrent();
            $table->integer('view_duration')->nullable();
            $table->integer('last_position')->nullable();
            $table->boolean('completed')->default(false);
            $table->string('device_type', 50)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->string('ip_address', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_history');
    }
};
