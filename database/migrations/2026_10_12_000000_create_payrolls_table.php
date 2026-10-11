<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employees_id')->constrained('employees')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('days_present')->default(0);
            $table->decimal('base_pay', 12, 2)->default(0);
            $table->decimal('meal_pay', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('total_pay', 12, 2)->default(0);
            $table->enum('status', ['Belum Dibayar', 'Dibayar'])->default('Belum Dibayar');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            // Satu karyawan hanya punya satu data gaji per periode
            $table->unique(['employees_id', 'period_start', 'period_end']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}