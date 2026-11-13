<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSickPayAndPaidByToPayrollsTable extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('sick_pay', 12, 2)->default(0)->after('overtime_pay');
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['sick_pay', 'paid_by']);
        });
    }
}