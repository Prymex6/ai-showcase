<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Leads/Index', [
            'leads'     => Lead::latest()->get(),
            'templates' => EmailTemplate::where('is_active', true)->get(),
            'statuses'  => Lead::$statuses,
        ]);
    }

    public function kanban(): Response
    {
        $grouped = Lead::latest()->get()->groupBy('status');
        return Inertia::render('Leads/Kanban', [
            'groups'   => $grouped,
            'statuses' => Lead::$statuses,
        ]);
    }

    public function templates(): Response
    {
        return Inertia::render('Leads/Templates', [
            'templates' => EmailTemplate::latest()->get(),
        ]);
    }

    public function stats(): Response
    {
        $leads = Lead::all();
        return Inertia::render('Leads/Stats', [
            'total'        => $leads->count(),
            'byStatus'     => $leads->groupBy('status')->map->count(),
            'byService'    => $leads->groupBy('service')->map->count(),
            'avgScore'     => round($leads->avg('score'), 1),
            'wonCount'     => $leads->where('status', 'closed_won')->count(),
            'conversionRate' => $leads->count() > 0
                ? round($leads->where('status', 'closed_won')->count() / $leads->count() * 100, 1)
                : 0,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:30',
            'service' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $lead = Lead::create($validated + ['score' => rand(40, 80)]);

        return back()->with('success', 'Lead dodany: ' . $lead->name);
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|in:' . implode(',', array_keys(Lead::$statuses))]);
        $lead->update(['status' => $request->status]);
        return back()->with('success', 'Status zaktualizowany.');
    }

    public function update(Request $request, Lead $lead)
    {
        $lead->update($request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:30',
            'service' => 'nullable|string|max:255',
            'score'   => 'nullable|integer|min:0|max:100',
            'notes'   => 'nullable|string',
        ]));
        return back()->with('success', 'Lead zaktualizowany.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return back()->with('success', 'Lead usunięty.');
    }

    public function storeTemplate(Request $request)
    {
        EmailTemplate::create($request->validate([
            'name'      => 'required|string|max:255',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'trigger'   => 'nullable|string',
            'is_active' => 'boolean',
        ]));
        return back()->with('success', 'Szablon zapisany.');
    }

    public function destroyTemplate(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return back()->with('success', 'Szablon usunięty.');
    }
}
