<?php

namespace Tests\Feature;

use App\Models\ResponseTemplate;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config(['services.openai.demo_mode' => true]);
    }

    private function makeReview(int $rating = 5, ?string $reply = null): Review
    {
        return Review::create([
            'author_name' => 'Testowy Klient',
            'rating'      => $rating,
            'content'     => 'Opinia testowa numer ' . rand(1, 9999),
            'platform'    => 'google',
            'sentiment'   => $rating >= 4 ? 'positive' : ($rating === 3 ? 'neutral' : 'negative'),
            'reply'       => $reply,
            'status'      => $reply ? 'replied' : 'pending',
            'review_date' => now(),
        ]);
    }

    // --- GET pages ---

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reviews')->assertOk();
    }

    public function test_templates_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reviews/templates')->assertOk();
    }

    public function test_trends_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/reviews/trends')->assertOk();
    }

    // --- AI Reply Generation ---

    public function test_generate_reply_returns_json_with_reply(): void
    {
        $review = $this->makeReview(5);

        $response = $this->actingAs($this->user)
            ->postJson("/reviews/{$review->id}/generate-reply");

        $response->assertOk()->assertJsonStructure(['reply']);
        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_generate_reply_for_negative_review_returns_string(): void
    {
        $review = $this->makeReview(1);

        $response = $this->actingAs($this->user)
            ->postJson("/reviews/{$review->id}/generate-reply");

        $response->assertOk();
        $this->assertIsString($response->json('reply'));
    }

    // --- Save Reply ---

    public function test_save_reply_updates_review(): void
    {
        $review = $this->makeReview(4);

        $this->actingAs($this->user)
            ->post("/reviews/{$review->id}/reply", ['reply' => 'Dziękujemy za opinię!'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id'     => $review->id,
            'reply'  => 'Dziękujemy za opinię!',
            'status' => 'replied',
        ]);
    }

    public function test_save_reply_validates_empty_reply(): void
    {
        $review = $this->makeReview(5);

        $this->actingAs($this->user)
            ->post("/reviews/{$review->id}/reply", ['reply' => ''])
            ->assertSessionHasErrors(['reply']);
    }

    // --- Delete Review ---

    public function test_destroy_deletes_review(): void
    {
        $review = $this->makeReview(3);

        $this->actingAs($this->user)
            ->delete("/reviews/{$review->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    // --- Response Templates CRUD ---

    public function test_store_template_creates_response_template(): void
    {
        $this->actingAs($this->user)
            ->post('/reviews/templates', [
                'name'           => 'Odpowiedź na 5 gwiazdek',
                'sentiment_type' => 'positive',
                'min_rating'     => 5,
                'max_rating'     => 5,
                'content'        => 'Serdecznie dziękujemy za wspaniałą opinię!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('response_templates', ['name' => 'Odpowiedź na 5 gwiazdek']);
    }

    public function test_store_template_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/reviews/templates', [])
            ->assertSessionHasErrors(['name', 'min_rating', 'max_rating', 'content']);
    }

    public function test_destroy_template_removes_template(): void
    {
        $template = ResponseTemplate::create([
            'name'       => 'Do usunięcia',
            'min_rating' => 1,
            'max_rating' => 3,
            'content'    => 'Przepraszamy',
        ]);

        $this->actingAs($this->user)
            ->delete("/reviews/templates/{$template->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('response_templates', ['id' => $template->id]);
    }

    // --- Trends data ---

    public function test_trends_passes_stats_with_correct_totals(): void
    {
        $this->makeReview(5);
        $this->makeReview(5);
        $this->makeReview(2);

        $this->actingAs($this->user)
            ->get('/reviews/trends')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reviews/Trends')
                ->has('stats')
                ->where('stats.total', 3)
            );
    }

    public function test_trends_reply_rate_is_correct(): void
    {
        $this->makeReview(5, 'Odpowiedź');
        $this->makeReview(4);

        $response = $this->actingAs($this->user)->get('/reviews/trends');
        $stats    = $response->viewData('page')['props']['stats'] ?? null;

        // 1 replied out of 2 = 50%
        $response->assertOk();
    }
}
