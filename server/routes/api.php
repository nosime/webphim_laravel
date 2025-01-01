<?php

use App\Http\Controllers\Sync\CategorySyncController;
use App\Http\Controllers\Sync\CountrySyncController;
use App\Http\Controllers\Sync\CleanMovieController;
use App\Http\Controllers\Sync\MovieSyncController;
use App\Http\Controllers\Api\ListMoviesController;
use App\Http\Controllers\Api\ListMoviesRDController;
use App\Http\Controllers\Api\ListMoviesTopController;
use App\Http\Controllers\Api\MovieDetailController;
use App\Http\Controllers\Api\LanguagesController;
use App\Http\Controllers\Api\FilterMoviesController;
use App\Http\Controllers\Api\SearchMoviesController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\MovieRatingController;
use App\Http\Controllers\Api\WatchLaterController;
use App\Http\Controllers\Api\ViewHistoryController;
use App\Http\Controllers\Api\Admin\ListUserController;
use App\Http\Controllers\Api\Admin\ServerController;
use App\Http\Controllers\Api\Admin\AddMovieController;
use App\Http\Controllers\Api\Admin\EditMovieController;
use App\Http\Controllers\Api\Admin\EpisodeController;
use App\Http\Controllers\Api\Admin\DeleteMovieController;


use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// Sync routes
Route::prefix('sync')->group(function () {
    // Sync single movie by slug
    Route::get('/movie/{slug}', [MovieSyncController::class, 'syncMovieBySlug'])
        ->name('sync.movie.single');

    // Sync multiple movies across pages
    Route::get('/movies', [MovieSyncController::class, 'syncAllMovies'])
        ->name('sync.movies.multiple');
    Route::get('/clear', [CleanMovieController::class, 'cleanAllMovieData'])
        ->name('sync.movies.multiple');
});

Route::prefix('sync/countries')->group(function () {
    Route::get('/', [CountrySyncController::class, 'getCountries']);
    Route::get('/{id}', [CountrySyncController::class, 'getCountryById']);
    Route::post('/sync', [CountrySyncController::class, 'syncCountries']);
});
Route::prefix('sync/categories')->group(function () {
    Route::get('/', [CategorySyncController::class, 'getCategories']);
    Route::get('/{id}', [CategorySyncController::class, 'getCategoryById']);
    Route::post('/sync', [CategorySyncController::class, 'syncCategories']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Movie routes
Route::get('/movie/{slug}', [MovieDetailController::class, 'getMovieBySlug']);

// Paginated movies list routes
Route::get('/movies', [ListMoviesController::class, 'getMoviesListPaginatedLimit']);
Route::get('/movies/{page}', [ListMoviesController::class, 'getMoviesListPaginatedLimit']);
Route::get('/movies/{page}/{limit}', [ListMoviesController::class, 'getMoviesListPaginatedLimit']);

// Random movies routes
Route::get('/movies-rdns/{limit?}', [ListMoviesRDController::class, 'getRandomMovies']);

// Random movies with pagination
Route::get('/movies-rdn', [ListMoviesRDController::class, 'getMoviesListPaginatedRandom']);
Route::get('/movies-rdn/{page}', [ListMoviesRDController::class, 'getMoviesListPaginatedRandom']);
Route::get('/movies-rdn/{page}/{limit}', [ListMoviesRDController::class, 'getMoviesListPaginatedRandom']);

// Top viewed movies
Route::get('/top-views', [ListMoviesTopController::class, 'getTopViewMovies']);

// Languages
Route::get('/languages', [LanguagesController::class, 'getUniqueLanguages']);

// Filter movies
Route::get('/filter', [FilterMoviesController::class, 'filterMovies']);

// Search routes
Route::get('/search', [SearchMoviesController::class, 'searchMovies']);
Route::get('/search-type', [SearchMoviesController::class, 'searchMoviesType']);

// Categories routes
Route::get('/categories', [CategoriesController::class, 'getCategories']);
Route::get('/categories/{id}', [CategoriesController::class, 'getCategoryById']);
// Country routes
Route::get('/countries', [CountriesController::class, 'getCountries']);
Route::get('/countries/{id}', [CountriesController::class, 'getCountryById']);

Route::group([
    'middleware' => ['api', 'auth:api'],
], function ($router) {
    // Movie rating routes
    Route::post('movies/toggle-like', [MovieRatingController::class, 'toggleLike']);
    Route::get('favorites', [MovieRatingController::class, 'getFavorites']);
    Route::get('favorites/{userId}', [MovieRatingController::class, 'getFavorites']);

    // Watch later routes
    Route::post('watch-later/toggle', [WatchLaterController::class, 'toggleWatchLater']);
    Route::get('watch-later', [WatchLaterController::class, 'getWatchLater']);
    Route::get('watch-later/{userId}', [WatchLaterController::class, 'getWatchLater']);

    // View history routes
    Route::post('/view-history', [ViewHistoryController::class, 'saveViewHistory']);
    Route::get('/view-history-user', [ViewHistoryController::class, 'getUserHistory']);
    Route::get('/view-history-user/{userId?}', [ViewHistoryController::class, 'getUserHistory']);


});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'admin'
], function ($router) {
    Route::get('users', [ListUserController::class, 'getUsers']);
    Route::delete('users/{id}', [ListUserController::class, 'deleteUser']);

    // Server routes
    Route::get('servers', [ServerController::class, 'getServers']);
    Route::post('servers', [ServerController::class, 'addServer']);
    Route::put('servers/{id}', [ServerController::class, 'updateServer']);
    Route::delete('servers/{id}', [ServerController::class, 'deleteServer']);

    // Movie routes
    Route::post('add-movies', [AddMovieController::class, 'addMovie']);
    Route::put('update-movies/{id}', [EditMovieController::class, 'updateMovie']);
    Route::delete('delete-movies/{id}', [DeleteMovieController::class, 'deleteMovie']);

    // Episode routes
    Route::get('episodes/{movieId}', [EpisodeController::class, 'getEpisodes']);
    Route::post('episodes', [EpisodeController::class, 'addEpisode']);
    Route::put('episodes/{id}', [EpisodeController::class, 'updateEpisode']);
    Route::delete('episodes/{id}', [EpisodeController::class, 'deleteEpisode']);
});
