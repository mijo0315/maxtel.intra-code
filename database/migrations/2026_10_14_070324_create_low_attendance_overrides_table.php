
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLowAttendanceOverridesTable extends Migration
{
    public function up()
    {
        Schema::connection('intra_payroll')->create(
            'low_attendance_overrides',
            function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('bio_id', 50);
                $table->date('attendance_date');

                $table->unsignedBigInteger('overridden_by')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(
                    ['bio_id', 'attendance_date'],
                    'low_attendance_bio_date_unique'
                );

                $table->index('attendance_date');
            }
        );
    }

    public function down()
    {
        Schema::connection('intra_payroll')
            ->dropIfExists('low_attendance_overrides');
    }
}
