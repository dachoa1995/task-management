<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('google_id')->unique();
            $table->string('email', 50)->unique();
            $table->string('name', 50);
            $table->string('avatarURL', 200);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('description', 500);
            $table->timestamps();
        });

        Schema::create('projects_users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id');
            $table->unsignedInteger('project_id');
            $table->foreign('user_id')->references('google_id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->timestamps();
        });

        Schema::create('status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->string('name', 50);
            $table->integer('order');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('status_id');
            $table->foreign('status_id')->references('id')->on('status');
            $table->string('name', 50);
            $table->string('description', 500);
            $table->date('deadline');
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id');
            $table->unsignedInteger('task_id');
            $table->foreign('user_id')->references('google_id')->on('users');
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->string('content', 500);
            $table->timestamps();
        });

        Schema::create('tasks_users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id');
            $table->unsignedInteger('task_id');
            $table->foreign('user_id')->references('google_id')->on('users');
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('projects_users');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('status');
        Schema::dropIfExists('tasks_users');
    }
}