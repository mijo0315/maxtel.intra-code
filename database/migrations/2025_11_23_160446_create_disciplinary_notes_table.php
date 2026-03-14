<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisciplinaryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('disciplinary_notes')) {
            Schema::create('disciplinary_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('case_details');
                $table->text('remarks');
                $table->date('date_served');
                $table->string('attachment_path')->nullable();
                $table->timestamps();

                $table->foreign('employee_id')
                      ->references('id')
                      ->on('tbl_employee')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disciplinary_notes');
    }
}