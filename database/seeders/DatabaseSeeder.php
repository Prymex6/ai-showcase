<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\EmailTemplate;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KnowledgeBase;
use App\Models\Conversation;
use App\Models\SocialPost;
use App\Models\ContentTemplate;
use App\Models\KpiGoal;
use App\Models\Review;
use App\Models\ResponseTemplate;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@ai-showcase.pl'], [
            'name'     => 'Admin',
            'password' => bcrypt('admin123'),
        ]);

        $services = ['Automatyzacja AI', 'Lead Manager', 'FakturaAI', 'Social Planner', 'Raporty AI'];
        $statuses = ['new', 'contact', 'qualified', 'proposal', 'closed_won'];
        foreach (range(1, 12) as $i) {
            Lead::create([
                'name'    => "Klient $i Testowy",
                'company' => "Firma $i Sp. z o.o.",
                'email'   => "klient{$i}@example.com",
                'phone'   => '+48 500 ' . str_pad($i * 111, 6, '0', STR_PAD_LEFT),
                'service' => $services[array_rand($services)],
                'message' => 'Jestem zainteresowany Państwa ofertą. Proszę o kontakt.',
                'status'  => $statuses[array_rand($statuses)],
                'score'   => rand(30, 95),
                'source'  => 'website',
            ]);
        }

        EmailTemplate::insert([
            ['name' => 'Powitanie — nowy lead', 'subject' => 'Dziękujemy za kontakt — {{firma}}', 'body' => 'Dzień dobry {{imie}},<br><br>Dziękujemy za zainteresowanie naszą ofertą. Nasz konsultant skontaktuje się w ciągu 24h.', 'trigger' => 'new_lead', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Oferta — pakiety', 'subject' => 'Nasza oferta dla {{firma}}', 'body' => 'Dzień dobry {{imie}},<br><br>Przesyłamy spersonalizowaną ofertę. Zapraszamy do kontaktu!', 'trigger' => 'manual', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Follow-up', 'subject' => 'Przypomnienie — Twoje zapytanie', 'body' => 'Dzień dobry {{imie}},<br><br>Chcieliśmy zapytać, czy masz już jakieś pytania dotyczące naszej oferty?', 'trigger' => 'follow_up', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $clients = [];
        foreach (range(1, 5) as $i) {
            $clients[] = Client::create([
                'name'        => "Jan Kowalski $i",
                'email'       => "jan{$i}@firma.pl",
                'phone'       => '+48 600 00' . $i . '000',
                'company'     => "Firma Testowa $i Sp. z o.o.",
                'nip'         => '123456789' . $i,
                'address'     => "ul. Testowa $i",
                'city'        => 'Warszawa',
                'postal_code' => '00-00' . $i,
            ]);
        }
        foreach ($clients as $idx => $client) {
            $invoice = Invoice::create([
                'number'      => 'FV/' . date('Y') . '/' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'client_id'   => $client->id,
                'status'      => ['draft', 'sent', 'paid'][array_rand([0, 1, 2])],
                'issue_date'  => now()->subDays(rand(1, 30)),
                'due_date'    => now()->addDays(rand(7, 30)),
                'net_total'   => 2000,
                'vat_total'   => 460,
                'gross_total' => 2460,
                'currency'    => 'PLN',
            ]);
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Wdrożenie systemu AI Showcase',
                'quantity'    => 1,
                'unit_price'  => 2000,
                'vat_rate'    => 23,
                'net_total'   => 2000,
                'gross_total' => 2460,
            ]);
        }

        KnowledgeBase::insert([
            ['question' => 'Kiedy jesteście otwarci?', 'answer' => 'Pracujemy poniedziałek–piątek 8:00–18:00, soboty 9:00–14:00.', 'keywords' => 'godziny,otwarcie,praca', 'action' => 'auto', 'confidence' => 95, 'is_active' => true, 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Ile kosztuje wdrożenie?', 'answer' => 'Pakiet Basic od 2 460 zł, Standard od 4 920 zł, Premium od 7 380 zł.', 'keywords' => 'cena,koszt,wycena,ile', 'action' => 'auto', 'confidence' => 90, 'is_active' => true, 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Jak mogę się z wami skontaktować?', 'answer' => 'Telefon: +48 500 123 456, e-mail: kontakt@ai-showcase.pl', 'keywords' => 'kontakt,telefon,email', 'action' => 'auto', 'confidence' => 95, 'is_active' => true, 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Reklamacja lub problem techniczny', 'answer' => 'Przepraszamy za niedogodności. Eskalujemy sprawę do specjalisty.', 'keywords' => 'reklamacja,problem,błąd,awaria', 'action' => 'escalate', 'confidence' => 80, 'is_active' => true, 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        SocialPost::insert([
            ['content' => '🚀 Nowe możliwości dla Twojego biznesu! Sprawdź jak AI może zautomatyzować Twoje procesy.', 'platforms' => json_encode(['facebook', 'linkedin']), 'hashtags' => '#AI #automatyzacja #biznes', 'status' => 'scheduled', 'scheduled_at' => now()->addDays(1), 'published_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['content' => '💡 Czy wiesz, że 73% firm korzystających z AI raportuje wzrost produktywności?', 'platforms' => json_encode(['facebook', 'instagram', 'linkedin']), 'hashtags' => '#AI #produktywnosc', 'status' => 'published', 'scheduled_at' => now()->subDay(), 'published_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        ContentTemplate::insert([
            ['name' => 'Post motywacyjny', 'type' => 'motivation', 'content' => '💪 {{tip}} — każdy duży sukces zaczyna się od małego kroku!', 'hashtags' => '#motywacja #sukces', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Case Study', 'type' => 'case_study', 'content' => '📊 Jak {{klient}} zwiększył sprzedaż o {{wynik}} w {{czas}} dzięki {{produkt}}. ROI: {{roi}}', 'hashtags' => '#casestudy #wyniki', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Post edukacyjny', 'type' => 'education', 'content' => '🎓 Porada tygodnia: {{tip}}\n\nWdróż to w swojej firmie {{firma}}!', 'hashtags' => '#edukacja #tips', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        KpiGoal::insert([
            ['name' => 'Przychód miesięczny', 'metric' => 'revenue', 'target_value' => 300000, 'current_value' => 187500, 'unit' => 'zł', 'period' => 'monthly', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nowi klienci', 'metric' => 'new_clients', 'target_value' => 50, 'current_value' => 32, 'unit' => '', 'period' => 'monthly', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Konwersja leadów', 'metric' => 'conversion_rate', 'target_value' => 25, 'current_value' => 18.5, 'unit' => '%', 'period' => 'monthly', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Review::insert([
            ['author_name' => 'Anna Kowalska', 'rating' => 5, 'content' => 'Świetna obsługa i profesjonalne podejście! Polecam wszystkim.', 'platform' => 'google', 'sentiment' => 'positive', 'reply' => null, 'status' => 'pending', 'review_date' => now()->subDays(2), 'created_at' => now(), 'updated_at' => now()],
            ['author_name' => 'Marek Nowak', 'rating' => 4, 'content' => 'Dobra jakość usług, drobne opóźnienie ale ogólnie zadowolony.', 'platform' => 'google', 'sentiment' => 'positive', 'reply' => null, 'status' => 'pending', 'review_date' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now()],
            ['author_name' => 'Piotr Wiśniewski', 'rating' => 2, 'content' => 'Byłem zawiedziony — czas realizacji znacznie dłuższy niż obiecany.', 'platform' => 'google', 'sentiment' => 'negative', 'reply' => null, 'status' => 'pending', 'review_date' => now()->subDays(7), 'created_at' => now(), 'updated_at' => now()],
            ['author_name' => 'Katarzyna Zielińska', 'rating' => 5, 'content' => 'Rewelacyjny system! Zaoszczędził mi mnóstwo czasu.', 'platform' => 'google', 'sentiment' => 'positive', 'reply' => 'Dziękujemy za wspaniałą opinię! Cieszymy się, że możemy pomagać.', 'status' => 'replied', 'review_date' => now()->subDays(10), 'created_at' => now(), 'updated_at' => now()],
        ]);

        ResponseTemplate::insert([
            ['name' => 'Odpowiedź na 5 gwiazdek', 'min_rating' => 5, 'max_rating' => 5, 'content' => 'Dziękujemy za wspaniałą opinię, {{author}}! Cieszymy się i zapraszamy ponownie!', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Odpowiedź na 4 gwiazdki', 'min_rating' => 4, 'max_rating' => 4, 'content' => 'Dziękujemy za opinię, {{author}}! Pracujemy nad tym, by przy kolejnej wizycie zasłużyć na 5 gwiazdek!', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Odpowiedź na niską ocenę', 'min_rating' => 1, 'max_rating' => 3, 'content' => 'Przepraszamy za niedogodności, {{author}}. Proszę kontakt na kontakt@firma.pl — rozwiążemy problem.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Conversation::insert([
            ['session_id' => 'demo-1', 'role' => 'user', 'message' => 'Kiedy jesteście otwarci?', 'action' => 'auto', 'confidence' => 95, 'created_at' => now()->subMinutes(30), 'updated_at' => now()->subMinutes(30)],
            ['session_id' => 'demo-1', 'role' => 'assistant', 'message' => 'Pracujemy poniedziałek–piątek 8:00–18:00, soboty 9:00–14:00. Zapraszamy!', 'action' => 'auto', 'confidence' => 95, 'created_at' => now()->subMinutes(29), 'updated_at' => now()->subMinutes(29)],
        ]);
    }
}
