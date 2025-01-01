<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            // Sửa constrained() để chỉ định đúng tên cột primary key
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')
                  ->references('role_id')  // Tham chiếu đến role_id thay vì id
                  ->on('roles')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')
                  ->references('permission_id')  // Tham chiếu đến permission_id
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
