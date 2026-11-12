<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSickLeaveToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('attendance_status')->default('hadir')->after('date');
            $table->string('sick_leave_proof')->nullable()->after('attendance_status');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['attendance_status', 'sick_leave_proof']);
        });
    }
}
