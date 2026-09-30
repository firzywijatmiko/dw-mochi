<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyReportsTable extends Migration
{
    public function up()
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->date('week_end');
            $table->integer('total_orders');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_expense', 15, 2);
            $table->decimal('total_material_expense', 15, 2);
            $table->decimal('total_wage_expense', 15, 2);
            $table->decimal('net_profit', 15, 2);
            $table->dateTime('generated_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_reports');
    }
}