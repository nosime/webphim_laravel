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
        Schema::create('user_roles', function (Blueprint $table) {
            // Sửa lại foreign key để tham chiếu đến user_id thay vì id
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('user_id')  // references user_id
                  ->on('users')
                  ->onDelete('cascade');

            // Tương tự cho role_id
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')
                  ->references('role_id')  // references role_id
                  ->on('roles')
                  ->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
