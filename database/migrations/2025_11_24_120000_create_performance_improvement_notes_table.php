<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformanceImprovementNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('performance_improvement_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('case_details');
            $table->longText('remarks');
            $table->date('date_served');
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            // Foreign keys (commented out temporarily if constraint issues)
            // Uncomment when tbl_employee table structure is verified
            // $table->foreign('employee_id')->references('id')->on('tbl_employee')->onDelete('cascade');
            // $table->foreign('parent_id')->references('id')->on('performance_improvement_notes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performance_improvement_notes');
    }
}
