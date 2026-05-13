<?php

namespace Tests\Unit;

use App\Models\KpiGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_is_zero_when_target_is_zero(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Test',
            'target_value'  => 0,
            'current_value' => 0,
            'unit'          => 'PLN',
            'period'        => 'monthly',
        ]);

        $this->assertEquals(0, $goal->progress);
    }

    public function test_progress_calculates_correct_percentage(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Przychód',
            'target_value'  => 100,
            'current_value' => 75,
            'unit'          => 'PLN',
            'period'        => 'monthly',
        ]);

        $this->assertEquals(75.0, $goal->progress);
    }

    public function test_progress_is_capped_at_100(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Konwersja',
            'target_value'  => 100,
            'current_value' => 150,
            'unit'          => '%',
            'period'        => 'monthly',
        ]);

        $this->assertEquals(100, $goal->progress);
    }

    public function test_progress_is_appended_to_json(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Test',
            'target_value'  => 200,
            'current_value' => 100,
            'unit'          => 'PLN',
            'period'        => 'monthly',
        ]);

        $array = $goal->toArray();

        $this->assertArrayHasKey('progress', $array);
        $this->assertEquals(50.0, $array['progress']);
    }

    public function test_progress_rounds_to_one_decimal(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Klienci',
            'target_value'  => 30,
            'current_value' => 10,
            'unit'          => 'szt',
            'period'        => 'monthly',
        ]);

        $this->assertEquals(33.3, $goal->progress);
    }
}
