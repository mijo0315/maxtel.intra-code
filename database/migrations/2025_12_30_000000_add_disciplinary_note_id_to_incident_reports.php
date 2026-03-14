<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisciplinaryNoteIdToIncidentReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('incident_reports', 'disciplinary_note_id')) {
            Schema::table('incident_reports', function (Blueprint $table) {
                $table->unsignedBigInteger('disciplinary_note_id')->nullable()->after('id');
                $table->foreign('disciplinary_note_id')
                      ->references('id')
                      ->on('disciplinary_notes')
                      ->onDelete('set null');
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
        if (Schema::hasColumn('incident_reports', 'disciplinary_note_id')) {
            Schema::table('incident_reports', function (Blueprint $table) {
                $table->dropForeign(['disciplinary_note_id']);
                $table->dropColumn('disciplinary_note_id');
            });
        }
    }
}
