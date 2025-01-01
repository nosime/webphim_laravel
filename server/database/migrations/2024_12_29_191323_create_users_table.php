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
    Schema::create('users', function (Blueprint $table) {
        $table->id('user_id');
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('password');
        $table->string('display_name', 100)->nullable();
        $table->string('avatar', 500)->nullable();
        $table->string('cover_photo', 500)->nullable();
        $table->string('bio', 500)->nullable();
        $table->string('gender', 10)->nullable();
        $table->date('birthday')->nullable();
        $table->string('phone_number', 20)->nullable();
        $table->string('address')->nullable();
        $table->boolean('is_verified')->default(false);
        $table->boolean('is_active')->default(true);
        $table->boolean('is_premium')->default(false);
        $table->timestamp('premium_expire_date')->nullable();
        $table->timestamp('last_login_at')->nullable();
        $table->string('last_login_ip', 50)->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
