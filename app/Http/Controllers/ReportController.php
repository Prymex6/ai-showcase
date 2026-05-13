<?php

namespace App\Http\Controllers;

use App\Models\KpiGoal;
use App\Models\ScheduledReport;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private OpenAIService $ai) {}

    public function dashboard(): Response
    {
        return Inertia::render('Reports/Dashboard', [
            'goals'     => KpiGoal::where('is_active', true)->get(),
            'schedules' => ScheduledReport::latest()->get(),
        ]);
    }

    public function goals(): Response
    {
        return Inertia::render('Reports/Goals', [
            'goals' => KpiGoal::latest()->get(),
        ]);
    }

    public function schedule(): Response
    {
        return Inertia::render('Reports/Schedule', [
            'schedules' => ScheduledReport::latest()->get(),
        ]);
    }

    public function generateInsights(Request $request)
    {
        $metrics = [
            'revenue'         => 187500,
            'new_clients'     => 32,
            'conversion_rate' => '18.5%',
            'avg_response_time' => '3.2 min',
            'reviews_positive' => '78%',
        ];

        $insights = $this->ai->analyzeMetrics($metrics);
        return response()->json(['insights' => $insights]);
    }

    public function storeGoal(Request $request)
    {
        KpiGoal::create($request->validate([
            'name'          => 'required|string|max:255',
            'target_value'  => 'required|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit'          => 'nullable|string|max:20',
            'period'        => 'required|in:monthly,quarterly,yearly',
        ]));
        return back()->with('success', 'Cel KPI dodany.');
    }

    public function updateGoal(Request $request, KpiGoal $kpiGoal)
    {
        $kpiGoal->update($request->validate([
            'name'          => 'required|string|max:255',
            'target_value'  => 'required|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit'          => 'nullable|string|max:20',
            'period'        => 'required|in:monthly,quarterly,yearly',
        ]));
        return back()->with('success', 'Cel KPI zaktualizowany.');
    }

    public function destroyGoal(KpiGoal $kpiGoal)
    {
        $kpiGoal->delete();
        return back()->with('success', 'Cel usunięty.');
    }

    public function storeSchedule(Request $request)
    {
        ScheduledReport::create($request->validate([
            'name'        => 'required|string|max:255',
            'report_type' => 'nullable|string|max:100',
            'frequency'   => 'required|in:daily,weekly,monthly',
            'recipients'  => 'required|string',
        ]));
        return back()->with('success', 'Harmonogram dodany.');
    }

    public function destroySchedule(ScheduledReport $scheduledReport)
    {
        $scheduledReport->delete();
        return back()->with('success', 'Harmonogram usunięty.');
    }
}
