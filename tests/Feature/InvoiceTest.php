<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user   = User::factory()->create();
        $this->client = Client::create([
            'name'  => 'Testowy Klient',
            'email' => 'klient@test.pl',
        ]);
    }

    // --- GET pages ---

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/invoices')->assertOk();
    }

    public function test_clients_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/invoices/clients')->assertOk();
    }

    public function test_recurring_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/invoices/recurring')->assertOk();
    }

    public function test_integrations_returns_ok(): void
    {
        $this->actingAs($this->user)->get('/invoices/integrations')->assertOk();
    }

    // --- Invoice CRUD ---

    public function test_store_creates_invoice_with_items(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => now()->toDateString(),
                'due_date'   => now()->addDays(14)->toDateString(),
                'items'      => [
                    ['description' => 'Usługa projektowania', 'quantity' => 2, 'unit_price' => 500, 'vat_rate' => 23],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['client_id' => $this->client->id]);
        $this->assertDatabaseHas('invoice_items', ['description' => 'Usługa projektowania', 'quantity' => 2]);
    }

    public function test_store_calculates_totals_correctly(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => now()->toDateString(),
                'due_date'   => now()->addDays(14)->toDateString(),
                'items'      => [
                    ['description' => 'Licencja', 'quantity' => 1, 'unit_price' => 1000, 'vat_rate' => 23],
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'net_total'   => 1000.00,
            'vat_total'   => 230.00,
            'gross_total' => 1230.00,
        ]);
    }

    public function test_store_invoice_generates_number(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => now()->toDateString(),
                'due_date'   => now()->addDays(14)->toDateString(),
                'items'      => [
                    ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 0],
                ],
            ]);

        $invoice = Invoice::first();
        $this->assertStringStartsWith('FV/' . date('Y') . '/', $invoice->number);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices', [])
            ->assertSessionHasErrors(['client_id', 'issue_date', 'due_date', 'items']);
    }

    public function test_update_status_changes_invoice_status(): void
    {
        $invoice = $this->createInvoice();

        $this->actingAs($this->user)
            ->patch("/invoices/{$invoice->id}/status", ['status' => 'paid'])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_destroy_deletes_invoice(): void
    {
        $invoice = $this->createInvoice();

        $this->actingAs($this->user)
            ->delete("/invoices/{$invoice->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    // --- Client CRUD ---

    public function test_store_client_creates_client(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices/clients', [
                'name'    => 'Nowy Klient',
                'email'   => 'nowy@klient.pl',
                'company' => 'Firma XYZ',
                'nip'     => '1234567890',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', ['name' => 'Nowy Klient', 'email' => 'nowy@klient.pl']);
    }

    public function test_destroy_client_removes_client(): void
    {
        $client = Client::create(['name' => 'Do usunięcia', 'email' => 'del@del.pl']);

        $this->actingAs($this->user)
            ->delete("/invoices/clients/{$client->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    // --- Recurring ---

    public function test_store_recurring_creates_recurring_invoice(): void
    {
        $this->actingAs($this->user)
            ->post('/invoices/recurring', [
                'client_id'    => $this->client->id,
                'name'         => 'Abonament miesięczny',
                'amount'       => 1500,
                'vat_rate'     => 23,
                'interval'     => 'monthly',
                'day_of_month' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('recurring_invoices', [
            'client_id' => $this->client->id,
            'name'      => 'Abonament miesięczny',
            'interval'  => 'monthly',
        ]);
    }

    // --- Helpers ---

    private function createInvoice(): Invoice
    {
        $invoice = Invoice::create([
            'number'      => 'FV/' . date('Y') . '/TEST',
            'client_id'   => $this->client->id,
            'status'      => 'draft',
            'issue_date'  => now(),
            'due_date'    => now()->addDays(14),
            'net_total'   => 1000,
            'vat_total'   => 230,
            'gross_total' => 1230,
        ]);
        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => 'Pozycja testowa',
            'quantity'    => 1,
            'unit_price'  => 1000,
            'vat_rate'    => 23,
            'net_total'   => 1000,
            'gross_total' => 1230,
        ]);
        return $invoice;
    }
}
