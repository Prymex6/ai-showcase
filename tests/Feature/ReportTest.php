<?php

namespace Tests\Feature;

use App\Models\KpiGoal;
use App\Models\ScheduledReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config(['services.openai.demo_mode' => true]);
    }

    // --- GET pages ---

    public function test_dashboard_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reports')->assertOk();
    }

    public function test_goals_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reports/goals')->assertOk();
    }

    public function test_schedule_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reports/schedule')->assertOk();
    }

    // --- KPI Goals CRUD ---

    public function test_store_goal_creates_kpi_goal(): void
    {
        $this->actingAs($this->user)
            ->post('/reports/goals', [
                'name'          => 'Przychód miesięczny',
                'target_value'  => 300000,
                'current_value' => 150000,
                'unit'          => 'PLN',
                'period'        => 'monthly',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kpi_goals', ['name' => 'Przychód miesięczny', 'target_value' => 300000]);
    }

    public function test_store_goal_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/reports/goals', [])
            ->assertSessionHasErrors(['name', 'target_value', 'period']);
    }

    public function test_update_goal_modifies_kpi_goal(): void
    {
        $goal = KpiGoal::create([
            'name'          => 'Stary cel',
            'target_value'  => 100,
            'current_value' => 50,
            'unit'          => 'PLN',
            'period'        => 'monthly',
        ]);

        $this->actingAs($this->user)
            ->patch("/reports/goals/{$goal->id}", [
                'name'          => 'Zaktualizowany cel',
                'target_value'  => 200,
                'current_value' => 120,
                'unit'          => 'PLN',
                'period'        => 'quarterly',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kpi_goals', ['id' => $goal->id, 'name' => 'Zaktualizowany cel', 'target_value' => 200]);
    }

    public function test_destroy_goal_removes_kpi_goal(): void
    {
        $goal = KpiGoal::create([
            'name'         => 'Do usunięcia',
            'target_value' => 100,
            'unit'         => 'PLN',
            'period'       => 'monthly',
        ]);

        $this->actingAs($this->user)
            ->delete("/reports/goals/{$goal->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('kpi_goals', ['id' => $goal->id]);
    }

    // --- Schedule CRUD ---

    public function test_store_schedule_creates_scheduled_report(): void
    {
        $this->actingAs($this->user)
            ->post('/reports/schedule', [
                'name'        => 'Tygodniowy KPI',
                'report_type' => 'kpi_summary',
                'frequency'   => 'weekly',
                'recipients'  => 'ceo@firma.pl, manager@firma.pl',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('scheduled_reports', ['name' => 'Tygodniowy KPI', 'frequency' => 'weekly']);
    }

    public function test_store_schedule_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/reports/schedule', [])
            ->assertSessionHasErrors(['name', 'frequency', 'recipients']);
    }

    public function test_destroy_schedule_removes_scheduled_report(): void
    {
        $schedule = ScheduledReport::create([
            'name'        => 'Do usunięcia',
            'frequency'   => 'monthly',
            'recipients'  => 'test@test.pl',
            'report_type' => 'kpi_summary',
        ]);

        $this->actingAs($this->user)
            ->delete("/reports/schedule/{$schedule->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('scheduled_reports', ['id' => $schedule->id]);
    }

    // --- AI Insights ---

    public function test_generate_insights_returns_json(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/reports/insights');

        $response->assertOk()->assertJsonStructure(['insights']);
        $this->assertNotEmpty($response->json('insights'));
    }

    // --- Dashboard props ---

    public function test_dashboard_passes_goals_and_schedules(): void
    {
        KpiGoal::create(['name' => 'Cel testowy', 'target_value' => 100, 'unit' => 'PLN', 'period' => 'monthly', 'is_active' => true]);

        $this->actingAs($this->user)
            ->get('/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Dashboard')
                ->has('goals')
                ->has('schedules')
            );
    }
}
