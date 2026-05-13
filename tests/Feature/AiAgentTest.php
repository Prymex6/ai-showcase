<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAgentTest extends TestCase
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

    public function test_chat_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/agent')->assertOk();
    }

    public function test_knowledge_base_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/agent/knowledge')->assertOk();
    }

    public function test_history_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/agent/history')->assertOk();
    }

    public function test_settings_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/agent/settings')->assertOk();
    }

    // --- Knowledge Base CRUD ---

    public function test_store_kb_entry_creates_entry(): void
    {
        $this->actingAs($this->user)
            ->post('/agent/knowledge', [
                'category' => 'Godziny pracy',
                'question' => 'Jakie są godziny otwarcia?',
                'answer'   => 'Pracujemy 8:00–18:00.',
                'keywords' => 'godziny, otwarcie',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('knowledge_bases', ['question' => 'Jakie są godziny otwarcia?']);
    }

    public function test_store_kb_entry_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/agent/knowledge', [])
            ->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_update_kb_entry_modifies_entry(): void
    {
        $entry = KnowledgeBase::create([
            'category'   => 'Test',
            'question'   => 'Stare pytanie',
            'answer'     => 'Stara odpowiedź',
            'is_active'  => true,
        ]);

        $this->actingAs($this->user)
            ->patch("/agent/knowledge/{$entry->id}", [
                'question' => 'Nowe pytanie',
                'answer'   => 'Nowa odpowiedź',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('knowledge_bases', ['id' => $entry->id, 'question' => 'Nowe pytanie']);
    }

    public function test_destroy_kb_entry_deletes_entry(): void
    {
        $entry = KnowledgeBase::create([
            'question'  => 'Do usunięcia',
            'answer'    => 'Odpowiedź',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->delete("/agent/knowledge/{$entry->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('knowledge_bases', ['id' => $entry->id]);
    }

    // --- Chat / Message ---

    public function test_send_message_returns_json_reply(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/agent/message', ['message' => 'Cześć, potrzebuję pomocy']);

        $response->assertOk()->assertJsonStructure(['reply', 'action', 'confidence']);
    }

    public function test_send_message_stores_conversation(): void
    {
        $this->actingAs($this->user)
            ->postJson('/agent/message', ['message' => 'Jakie macie godziny?']);

        $this->assertDatabaseHas('conversations', ['role' => 'user', 'message' => 'Jakie macie godziny?']);
        $this->assertDatabaseHas('conversations', ['role' => 'assistant']);
    }

    public function test_send_message_validates_empty_input(): void
    {
        $this->actingAs($this->user)
            ->postJson('/agent/message', ['message' => ''])
            ->assertUnprocessable();
    }

    public function test_send_message_increments_kb_usage_count(): void
    {
        $entry = KnowledgeBase::create([
            'question'    => 'godziny otwarcia',
            'answer'      => 'Pracujemy 8-18',
            'keywords'    => 'godziny',
            'is_active'   => true,
            'usage_count' => 0,
        ]);

        $this->actingAs($this->user)
            ->postJson('/agent/message', ['message' => 'godziny otwarcia']);

        $this->assertEquals(1, $entry->fresh()->usage_count);
    }

    // --- History ---

    public function test_history_session_returns_messages_json(): void
    {
        Conversation::create(['session_id' => 'test-session-1', 'role' => 'user', 'message' => 'Pytanie', 'action' => 'auto', 'confidence' => 90]);
        Conversation::create(['session_id' => 'test-session-1', 'role' => 'assistant', 'message' => 'Odpowiedź', 'action' => 'auto', 'confidence' => 90]);

        $response = $this->actingAs($this->user)
            ->getJson('/agent/history/test-session-1');

        $response->assertOk()->assertJsonStructure(['messages']);
        $this->assertCount(2, $response->json('messages'));
    }
}
