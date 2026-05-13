<?php

namespace Tests\Unit;

use App\Services\OpenAIService;
use Tests\TestCase;

class OpenAIServiceTest extends TestCase
{
    private OpenAIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.demo_mode' => true]);
        config(['services.openai.key'       => '']);
        config(['services.openai.model'     => 'gpt-4o-mini']);
        $this->service = new OpenAIService();
    }

    public function test_is_not_configured_without_api_key(): void
    {
        $this->assertFalse($this->service->isConfigured());
    }

    public function test_chat_returns_string_in_demo_mode(): void
    {
        $result = $this->service->chat([['role' => 'user', 'content' => 'Cześć']]);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_demo_reply_responds_to_godziny_keyword(): void
    {
        $result = $this->service->chat([['role' => 'user', 'content' => 'Jakie są godziny otwarcia?']]);
        $this->assertStringContainsString('8:00', $result);
    }

    public function test_demo_reply_responds_to_cena_keyword(): void
    {
        $result = $this->service->chat([['role' => 'user', 'content' => 'Jaka jest cena usługi?']]);
        $this->assertStringContainsString('zł', $result);
    }

    public function test_demo_reply_responds_to_problem_keyword(): void
    {
        $result = $this->service->chat([['role' => 'user', 'content' => 'Mam problem z zamówieniem']]);
        $this->assertStringContainsString('Eskaluję', $result);
    }

    public function test_demo_reply_returns_default_for_unknown_question(): void
    {
        $result = $this->service->chat([['role' => 'user', 'content' => 'Zupełnie losowe pytanie xyz']]);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_customer_service_reply_returns_array_with_required_keys(): void
    {
        $result = $this->service->customerServiceReply('Pytanie testowe');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('answer', $result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('confidence', $result);
    }

    public function test_customer_service_confidence_is_numeric(): void
    {
        $result = $this->service->customerServiceReply('Test');
        $this->assertIsInt($result['confidence']);
        $this->assertGreaterThan(0, $result['confidence']);
        $this->assertLessThanOrEqual(100, $result['confidence']);
    }

    public function test_generate_post_returns_string(): void
    {
        $result = $this->service->generatePost('nowy produkt', 'Facebook');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_analyze_metrics_returns_string(): void
    {
        $result = $this->service->analyzeMetrics([
            'revenue'   => 50000,
            'clients'   => 15,
            'retention' => '85%',
        ]);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_reply_to_review_returns_string(): void
    {
        $result = $this->service->replyToReview('Super firma!', 5);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_ask_combines_system_and_user_message(): void
    {
        $result = $this->service->ask('Ile kosztuje?', 'Jesteś pomocnym asystentem.');
        $this->assertIsString($result);
    }
}
