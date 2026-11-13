<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSickLeaveStatusToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('sick_leave_status')->nullable()->after('sick_leave_proof');
        });

        DB::table('attendances')
            ->where('attendance_status', 'izin_sakit')
            ->update(['sick_leave_status' => 'pending']);
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('sick_leave_status');
        });
    }
}
