<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Invoices/Index', [
            'invoices' => Invoice::with('client')->latest()->get(),
            'statuses' => Invoice::$statuses,
        ]);
    }

    public function clients(): Response
    {
        return Inertia::render('Invoices/Clients', [
            'clients' => Client::withCount('invoices')->latest()->get(),
        ]);
    }

    public function recurring(): Response
    {
        return Inertia::render('Invoices/Recurring', [
            'recurring' => RecurringInvoice::with('client')->latest()->get(),
            'clients'   => Client::all(['id', 'name', 'company']),
        ]);
    }

    public function integrations(): Response
    {
        return Inertia::render('Invoices/Integrations');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date'   => 'required|date',
            'notes'      => 'nullable|string',
            'items'      => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.vat_rate'    => 'required|integer|in:0,5,8,23',
        ]);

        $netTotal = 0;
        $vatTotal = 0;
        foreach ($data['items'] as $item) {
            $net = $item['quantity'] * $item['unit_price'];
            $vat = $net * ($item['vat_rate'] / 100);
            $netTotal += $net;
            $vatTotal += $vat;
        }

        $year    = date('Y');
        $count   = Invoice::whereYear('created_at', $year)->count() + 1;
        $invoice = Invoice::create([
            'number'      => "FV/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT),
            'client_id'   => $data['client_id'],
            'issue_date'  => $data['issue_date'],
            'due_date'    => $data['due_date'],
            'notes'       => $data['notes'] ?? null,
            'net_total'   => round($netTotal, 2),
            'vat_total'   => round($vatTotal, 2),
            'gross_total' => round($netTotal + $vatTotal, 2),
        ]);

        foreach ($data['items'] as $item) {
            $net = round($item['quantity'] * $item['unit_price'], 2);
            $vat = round($net * ($item['vat_rate'] / 100), 2);
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'vat_rate'    => $item['vat_rate'],
                'net_total'   => $net,
                'gross_total' => $net + $vat,
            ]);
        }

        return back()->with('success', 'Faktura ' . $invoice->number . ' utworzona.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:' . implode(',', array_keys(Invoice::$statuses))]);
        $invoice->update(['status' => $request->status]);
        return back()->with('success', 'Status faktury zaktualizowany.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success', 'Faktura usunięta.');
    }

    public function storeClient(Request $request)
    {
        Client::create($request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:30',
            'company'     => 'nullable|string|max:255',
            'nip'         => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]));
        return back()->with('success', 'Klient dodany.');
    }

    public function destroyClient(Client $client)
    {
        $client->delete();
        return back()->with('success', 'Klient usunięty.');
    }

    public function storeRecurring(Request $request)
    {
        $data = $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'name'         => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'vat_rate'     => 'required|integer|in:0,5,8,23',
            'interval'     => 'required|in:monthly,quarterly,yearly',
            'day_of_month' => 'required|integer|min:1|max:28',
            'description'  => 'nullable|string',
        ]);

        RecurringInvoice::create($data + [
            'next_date' => now()->startOfMonth()->addDays($data['day_of_month'] - 1),
        ]);

        return back()->with('success', 'Faktura cykliczna dodana.');
    }
}
