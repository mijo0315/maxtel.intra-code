<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueDateAndResolutionToNteNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('nte_notes', 'due_date')) {
            Schema::table('nte_notes', function (Blueprint $table) {
                $table->date('due_date')->nullable()->after('date_served');
                $table->longText('resolution')->nullable()->after('attachment_path');
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
        if (Schema::hasColumn('nte_notes', 'due_date')) {
            Schema::table('nte_notes', function (Blueprint $table) {
                $table->dropColumn(['due_date', 'resolution']);
            });
        }
    }
}
