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
    Schema::table('movies', function (Blueprint $table) {
        $table->index('slug');
        $table->index('year');
        $table->index('views');
    });

    Schema::table('episodes', function (Blueprint $table) {
        $table->index('movie_id');
    });

    Schema::table('view_history', function (Blueprint $table) {
        $table->index('user_id');
        $table->index('movie_id');
        $table->index(['movie_id', 'episode_id']);
        $table->index(['user_id', 'view_date']);
    });

    Schema::table('watch_later', function (Blueprint $table) {
        $table->index('user_id');
    });

    Schema::table('movie_ratings', function (Blueprint $table) {
        $table->index('movie_id');
    });
}

public function down()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->dropIndex(['slug', 'year', 'views']);
    });

    Schema::table('episodes', function (Blueprint $table) {
        $table->dropIndex(['movie_id']);
    });

    Schema::table('view_history', function (Blueprint $table) {
        $table->dropIndex(['user_id', 'movie_id']);
        $table->dropIndex(['movie_id', 'episode_id']);
        $table->dropIndex(['user_id', 'view_date']);
    });

    Schema::table('watch_later', function (Blueprint $table) {
        $table->dropIndex(['user_id']);
    });

    Schema::table('movie_ratings', function (Blueprint $table) {
        $table->dropIndex(['movie_id']);
    });
}
};
