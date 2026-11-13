<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->unique()->constrained('payrolls')->onDelete('restrict');
            $table->foreignId('employees_id')->constrained('employees')->onDelete('restrict');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('days_present')->default(0);
            $table->decimal('base_pay', 12, 2)->default(0);
            $table->decimal('meal_pay', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('sick_pay', 12, 2)->default(0);
            $table->decimal('total_pay', 12, 2)->default(0);
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}