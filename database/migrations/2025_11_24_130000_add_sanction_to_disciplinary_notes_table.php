<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSanctionToDisciplinaryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disciplinary_notes', function (Blueprint $table) {
            $table->string('sanction')->nullable()->after('date_served');
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
            $table->dropColumn('sanction');
        });
    }
}
