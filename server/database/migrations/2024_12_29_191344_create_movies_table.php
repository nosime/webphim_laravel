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
    Schema::create('movies', function (Blueprint $table) {
        $table->id('movie_id');
        $table->string('name');
        $table->string('origin_name')->nullable();
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->text('content')->nullable();
        $table->enum('type', ['single', 'series']);
        $table->enum('status', ['completed', 'ongoing', 'trailer']);
        $table->text('thumb_url')->nullable();
        $table->text('poster_url')->nullable();
        $table->text('banner_url')->nullable();
        $table->text('trailer_url')->nullable();
        $table->integer('duration')->nullable();
        $table->string('episode_current', 50)->nullable();
        $table->integer('episode_total')->nullable();
        $table->string('quality', 20)->nullable();
        $table->string('language', 50)->nullable();
        $table->integer('year')->nullable();
        $table->text('actors')->nullable();
        $table->text('directors')->nullable();
        $table->boolean('is_copyright')->default(false);
        $table->boolean('is_subtitled')->default(false);
        $table->boolean('is_premium')->default(false);
        $table->boolean('is_visible')->default(true);
        $table->integer('views')->default(0);
        $table->integer('views_day')->default(0);
        $table->integer('views_week')->default(0);
        $table->integer('views_month')->default(0);
        $table->decimal('rating_value', 3, 1)->default(0);
        $table->integer('rating_count')->default(0);
        $table->string('tmdb_id', 50)->nullable();
        $table->string('imdb_id', 50)->nullable();
        $table->decimal('tmdb_rating', 3, 1)->nullable();
        $table->integer('tmdb_vote_count')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
