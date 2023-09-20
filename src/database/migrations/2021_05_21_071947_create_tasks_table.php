<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->float('estimated_time', 6, 1)->nullable();
            $table->float('estimated_sum_time', 6, 1)->nullable();
            $table->boolean('task_status')->default(false);
            $table->text('category_name')->nullable();
            $table->text('task_memo')->nullable();
            $table->float('actual_time', 6, 1)->nullable();
            $table->unsignedBigInteger('sprint_id');

            $table->foreign('sprint_id')
                ->references('id')
                ->on('sprints')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
