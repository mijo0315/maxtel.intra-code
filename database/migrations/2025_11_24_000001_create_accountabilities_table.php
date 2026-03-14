<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('intra_payroll')->hasTable('tbl_accountabilities')) {
            Schema::connection('intra_payroll')->create('tbl_accountabilities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('item_name');
                $table->text('item_description')->nullable();
                $table->decimal('item_value', 15, 2)->nullable();
                $table->string('serial_number', 100)->nullable();
                $table->string('property_number', 100)->nullable();
                $table->date('date_assigned');
                $table->enum('status', ['assigned', 'returned', 'lost', 'damaged'])->default('assigned');
                $table->string('condition_assigned')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->timestamp('date_created')->nullable();
                $table->timestamp('date_updated')->nullable();
                $table->timestamps();
                
                $table->index('employee_id');
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
        Schema::connection('intra_payroll')->dropIfExists('tbl_accountabilities');
    }
}
