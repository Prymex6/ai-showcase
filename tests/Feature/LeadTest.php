<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // --- Auth guard ---

    public function test_unauthenticated_user_is_redirected_from_leads(): void
    {
        $this->get('/leads')->assertRedirect('/login');
    }

    // --- GET pages ---

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/leads')->assertOk();
    }

    public function test_kanban_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/leads/kanban')->assertOk();
    }

    public function test_templates_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/leads/templates')->assertOk();
    }

    public function test_stats_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/leads/stats')->assertOk();
    }

    // --- CRUD Lead ---

    public function test_store_creates_a_lead(): void
    {
        $this->actingAs($this->user)
            ->post('/leads', [
                'name'    => 'Jan Nowak',
                'email'   => 'jan@acme.pl',
                'phone'   => '600 100 200',
                'company' => 'Acme Sp. z o.o.',
                'service' => 'Wdrożenie ERP',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', ['name' => 'Jan Nowak', 'email' => 'jan@acme.pl']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/leads', [])
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_update_status_changes_lead_status(): void
    {
        $lead = Lead::create([
            'name'   => 'Test Lead',
            'email'  => 'test@test.pl',
            'status' => 'new',
            'score'  => 50,
        ]);

        $this->actingAs($this->user)
            ->patch("/leads/{$lead->id}/status", ['status' => 'qualified'])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'qualified']);
    }

    public function test_update_modifies_lead_data(): void
    {
        $lead = Lead::create([
            'name'   => 'Stara nazwa',
            'email'  => 'old@test.pl',
            'status' => 'new',
            'score'  => 50,
        ]);

        $this->actingAs($this->user)
            ->patch("/leads/{$lead->id}", [
                'name'  => 'Nowa nazwa',
                'email' => 'new@test.pl',
                'score' => 80,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'name' => 'Nowa nazwa']);
    }

    public function test_destroy_deletes_lead(): void
    {
        $lead = Lead::create([
            'name'   => 'Do usunięcia',
            'email'  => 'del@test.pl',
            'status' => 'new',
            'score'  => 0,
        ]);

        $this->actingAs($this->user)
            ->delete("/leads/{$lead->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    // --- Email Templates ---

    public function test_store_template_creates_email_template(): void
    {
        $this->actingAs($this->user)
            ->post('/leads/templates', [
                'name'    => 'Szablon powitalny',
                'subject' => 'Witamy w naszym systemie',
                'body'    => 'Treść e-maila powitalnego',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('email_templates', ['name' => 'Szablon powitalny']);
    }

    public function test_destroy_template_removes_email_template(): void
    {
        $template = EmailTemplate::create([
            'name'    => 'Do usunięcia',
            'subject' => 'Temat',
            'body'    => 'Treść',
        ]);

        $this->actingAs($this->user)
            ->delete("/leads/templates/{$template->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }

    public function test_kanban_passes_grouped_leads(): void
    {
        Lead::create(['name' => 'Lead A', 'email' => 'a@a.pl', 'status' => 'new', 'score' => 0]);
        Lead::create(['name' => 'Lead B', 'email' => 'b@b.pl', 'status' => 'qualified', 'score' => 0]);

        $this->actingAs($this->user)
            ->get('/leads/kanban')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Leads/Kanban')
                ->has('groups')
                ->has('statuses')
            );
    }
}
