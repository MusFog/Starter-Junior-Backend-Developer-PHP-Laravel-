<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('image_path')->nullable();
            $table->string('employee_name')->index();
            $table->uuid('user_id');
            $table->unsignedSmallInteger('level')->default(0);
            $table->string('email');
            $table->string('phone');
            $table->string('position_name');
            $table->uuid('position_id');
            $table->decimal('salary', 10, 2);
            $table->string('supervisor_name')->nullable();
            $table->uuid('supervisor_id')->nullable();
            $table->date('employment_date');
            $table->uuid('admin_created_id');
            $table->uuid('admin_updated_id');

            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table
                ->foreign('admin_created_id')
                ->references('id')
                ->on('users');
            $table
                ->foreign('admin_updated_id')
                ->references('id')
                ->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
