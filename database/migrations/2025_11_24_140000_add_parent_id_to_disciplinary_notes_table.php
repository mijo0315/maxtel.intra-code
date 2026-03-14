<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToDisciplinaryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disciplinary_notes', function (Blueprint $table) {
            // Add parent_id column if it doesn't exist
            if (!Schema::hasColumn('disciplinary_notes', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('employee_id');
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('disciplinary_notes')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disciplinary_notes', function (Blueprint $table) {
            if (Schema::hasColumn('disciplinary_notes', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
}
