<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private bool $demoMode;

    public function __construct()
    {
        $this->apiKey   = config('services.openai.key', '');
        $this->model    = config('services.openai.model', 'gpt-4o-mini');
        $this->demoMode = config('services.openai.demo_mode', true);
    }

    public function chat(array $messages, array $options = []): string
    {
        if ($this->demoMode || !$this->isConfigured()) {
            return $this->demoResponse(end($messages)['content'] ?? '');
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', array_merge([
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => 800,
                    'temperature' => 0.7,
                ], $options));

            return $response->json('choices.0.message.content', 'Brak odpowiedzi.');
        } catch (\Exception $e) {
            Log::error('OpenAI error: ' . $e->getMessage());
            return 'Błąd komunikacji z AI. Spróbuj ponownie.';
        }
    }

    public function ask(string $prompt, string $system = ''): string
    {
        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];
        return $this->chat($messages);
    }

    public function generatePost(string $topic, string $platform = 'Facebook', string $tone = 'profesjonalny'): string
    {
        $system = 'Jesteś ekspertem od marketingu i social media. Piszesz po polsku. Twórz angażujące posty.';
        $prompt = "Napisz post na {$platform} na temat: '{$topic}'. Ton: {$tone}. Dodaj emoji i hashtagi. Max 280 znaków dla Twitter, 500 dla reszty.";
        return $this->ask($prompt, $system);
    }

    public function replyToReview(string $review, int $rating, string $company = 'nasza firma'): string
    {
        $system = "Jesteś menedżerem firmy {$company}. Odpowiadasz na opinie Google po polsku, uprzejmie i profesjonalnie.";
        $tone   = $rating >= 4 ? 'serdecznie podziękuj' : 'przeproś i zaproponuj rozwiązanie';
        $prompt = "Opinia klienta ({$rating}/5 gwiazdek): \"{$review}\"\n\nProszę {$tone}. Max 150 słów.";
        return $this->ask($prompt, $system);
    }

    public function customerServiceReply(string $question, string $knowledgeContext = ''): array
    {
        if ($this->demoMode || !$this->isConfigured()) {
            return ['answer' => $this->demoCSReply($question), 'action' => 'auto', 'confidence' => 85];
        }

        $system = 'Jesteś asystentem obsługi klienta. Odpowiadasz po polsku, przyjaźnie i pomocnie.'
            . ($knowledgeContext ? "\n\nBaza wiedzy:\n{$knowledgeContext}" : '');

        $prompt = "Pytanie: \"{$question}\"\n\nOdpowiedz JSON: {\"answer\":\"...\",\"action\":\"auto|escalate\",\"confidence\":0-100}";
        $raw    = $this->ask($prompt, $system);
        $json   = json_decode(trim($raw), true);

        return $json ?: ['answer' => $raw, 'action' => 'auto', 'confidence' => 70];
    }

    public function analyzeMetrics(array $metrics): string
    {
        $data   = json_encode($metrics, JSON_UNESCAPED_UNICODE);
        $system = 'Jesteś analitykiem marketingowym. Dajesz konkretne, actionable rekomendacje po polsku.';
        $prompt = "Przeanalizuj dane i daj 3-4 kluczowe insighty:\n{$data}\n\nKażdy insight: obserwacja + rekomendacja.";
        return $this->ask($prompt, $system);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey)
            && !str_contains($this->apiKey, 'YOUR')
            && !str_contains($this->apiKey, 'HERE');
    }

    private function demoResponse(string $question): string
    {
        return $this->demoCSReply($question);
    }

    private function demoCSReply(string $q): string
    {
        $q = mb_strtolower($q);
        if (str_contains($q, 'godzin') || str_contains($q, 'otwart')) {
            return 'Pracujemy poniedziałek–piątek 8:00–18:00, soboty 9:00–14:00. Zapraszamy! 😊';
        }
        if (str_contains($q, 'cen') || str_contains($q, 'koszt') || str_contains($q, 'ile')) {
            return 'Pakiet Basic od 2 460 zł, Standard od 4 920 zł, Premium od 7 380 zł. Przygotujemy bezpłatną wycenę!';
        }
        if (str_contains($q, 'problem') || str_contains($q, 'reklamacj') || str_contains($q, 'błąd')) {
            return 'Przepraszamy za problem! Eskaluję sprawę do specjalisty — skontaktuje się w ciągu 1h. 🔧';
        }
        return 'Dziękuję za wiadomość! Chętnie pomogę. Czy możesz podać więcej szczegółów? Nasz zespół jest do dyspozycji pn–pt 8:00–18:00. 😊';
    }
}
