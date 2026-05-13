<?php

namespace Tests\Feature;

use App\Models\ContentTemplate;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialPlannerTest extends TestCase
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

    public function test_calendar_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/social')->assertOk();
    }

    public function test_templates_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/social/templates')->assertOk();
    }

    public function test_analytics_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/social/analytics')->assertOk();
    }

    // --- Posts CRUD ---

    public function test_store_post_creates_social_post(): void
    {
        $this->actingAs($this->user)
            ->post('/social', [
                'content'      => 'Nowy post testowy na Facebooku!',
                'platform'     => ['Facebook', 'Instagram'],
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('social_posts', ['content' => 'Nowy post testowy na Facebooku!']);
    }

    public function test_store_post_validates_content(): void
    {
        $this->actingAs($this->user)
            ->post('/social', ['content' => ''])
            ->assertSessionHasErrors(['content']);
    }

    public function test_store_post_defaults_to_scheduled_status(): void
    {
        $this->actingAs($this->user)
            ->post('/social', [
                'content'  => 'Post bez daty',
                'platform' => ['LinkedIn'],
            ]);

        $this->assertDatabaseHas('social_posts', [
            'content' => 'Post bez daty',
            'status'  => 'scheduled',
        ]);
    }

    public function test_destroy_post_deletes_social_post(): void
    {
        $post = SocialPost::create([
            'content'   => 'Post do usunięcia',
            'platforms' => ['Facebook'],
            'status'    => 'draft',
        ]);

        $this->actingAs($this->user)
            ->delete("/social/{$post->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('social_posts', ['id' => $post->id]);
    }

    // --- AI Generate ---

    public function test_generate_returns_json_with_content(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/social/generate', ['platforms' => ['Facebook'], 'topic' => 'nowa usługa']);

        $response->assertOk()->assertJsonStructure(['content']);
        $this->assertNotEmpty($response->json('content'));
    }

    // --- Templates CRUD ---

    public function test_store_template_creates_content_template(): void
    {
        $this->actingAs($this->user)
            ->post('/social/templates', [
                'name'      => 'Szablon promocyjny',
                'content'   => 'Sprawdź naszą ofertę! 🚀 #promo',
                'platforms' => ['Facebook', 'Instagram'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('content_templates', ['name' => 'Szablon promocyjny']);
    }

    public function test_destroy_template_removes_template(): void
    {
        $template = ContentTemplate::create([
            'name'    => 'Do usunięcia',
            'type'    => 'other',
            'content' => 'Treść',
        ]);

        $this->actingAs($this->user)
            ->delete("/social/templates/{$template->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('content_templates', ['id' => $template->id]);
    }

    // --- Analytics data ---

    public function test_analytics_passes_stats_to_inertia(): void
    {
        SocialPost::create(['content' => 'Post 1', 'platforms' => ['Facebook'], 'status' => 'published']);
        SocialPost::create(['content' => 'Post 2', 'platforms' => ['Instagram'], 'status' => 'scheduled']);

        $this->actingAs($this->user)
            ->get('/social/analytics')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Social/Analytics')
                ->has('stats')
                ->where('stats.total', 2)
            );
    }
}
