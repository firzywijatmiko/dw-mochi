<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Services\PayrollService;
use Carbon\Carbon;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    /**
     * @dataProvider proportionalWorkHours
     */
    public function test_daily_wage_is_proportional_to_actual_worked_hours(
        int $hours,
        float $expectedWage
    ) {
        $employee = new Employee([
            'base_daily_wage' => 80000,
            'overtime_rate_1x' => 10000,
            'overtime_rate_2x' => 15000,
        ]);

        $result = app(PayrollService::class)->calculateDay(
            $employee,
            Carbon::create(2026, 11, 5, 8, 0),
            Carbon::create(2026, 11, 5, 8 + $hours, 0)
        );

        $this->assertSame((float) $hours, $result['worked_hours']);
        $this->assertSame($expectedWage, $result['daily_wage']);
    }

    public static function proportionalWorkHours(): array
    {
        return [
            '2 hours' => [2, 20000.0],
            '4 hours' => [4, 40000.0],
            '5 hours' => [5, 50000.0],
            '6 hours' => [6, 60000.0],
            '8 hours' => [8, 80000.0],
            '9 hours with overtime' => [9, 90000.0],
        ];
    }
}
