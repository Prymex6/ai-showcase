<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\SocialPlannerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lead Manager
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/kanban', [LeadController::class, 'kanban'])->name('kanban');
        Route::get('/templates', [LeadController::class, 'templates'])->name('templates');
        Route::get('/stats', [LeadController::class, 'stats'])->name('stats');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::patch('/{lead}/status', [LeadController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
        Route::post('/templates', [LeadController::class, 'storeTemplate'])->name('templates.store');
        Route::delete('/templates/{emailTemplate}', [LeadController::class, 'destroyTemplate'])->name('templates.destroy');
    });

    // FakturaAI
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/clients', [InvoiceController::class, 'clients'])->name('clients');
        Route::get('/recurring', [InvoiceController::class, 'recurring'])->name('recurring');
        Route::get('/integrations', [InvoiceController::class, 'integrations'])->name('integrations');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/clients', [InvoiceController::class, 'storeClient'])->name('clients.store');
        Route::delete('/clients/{client}', [InvoiceController::class, 'destroyClient'])->name('clients.destroy');
        Route::post('/recurring', [InvoiceController::class, 'storeRecurring'])->name('recurring.store');
    });

    // AI Agent
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/', [AiAgentController::class, 'chat'])->name('chat');
        Route::get('/knowledge', [AiAgentController::class, 'knowledgeBase'])->name('knowledge');
        Route::get('/history', [AiAgentController::class, 'history'])->name('history');
        Route::get('/history/{sessionId}', [AiAgentController::class, 'historySession'])->name('history.session');
        Route::get('/settings', [AiAgentController::class, 'settings'])->name('settings');
        Route::post('/message', [AiAgentController::class, 'sendMessage'])->name('message');
        Route::post('/knowledge', [AiAgentController::class, 'storeKbEntry'])->name('knowledge.store');
        Route::patch('/knowledge/{knowledgeBase}', [AiAgentController::class, 'updateKbEntry'])->name('knowledge.update');
        Route::delete('/knowledge/{knowledgeBase}', [AiAgentController::class, 'destroyKbEntry'])->name('knowledge.destroy');
    });

    // Social Planner
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('/', [SocialPlannerController::class, 'calendar'])->name('calendar');
        Route::get('/templates', [SocialPlannerController::class, 'templates'])->name('templates');
        Route::get('/analytics', [SocialPlannerController::class, 'analytics'])->name('analytics');
        Route::post('/', [SocialPlannerController::class, 'storePost'])->name('posts.store');
        Route::delete('/{socialPost}', [SocialPlannerController::class, 'destroyPost'])->name('posts.destroy');
        Route::post('/generate', [SocialPlannerController::class, 'generateContent'])->name('generate');
        Route::post('/templates', [SocialPlannerController::class, 'storeTemplate'])->name('templates.store');
        Route::delete('/templates/{contentTemplate}', [SocialPlannerController::class, 'destroyTemplate'])->name('templates.destroy');
    });

    // Raporty AI
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/goals', [ReportController::class, 'goals'])->name('goals');
        Route::get('/schedule', [ReportController::class, 'schedule'])->name('schedule');
        Route::post('/insights', [ReportController::class, 'generateInsights'])->name('insights');
        Route::post('/goals', [ReportController::class, 'storeGoal'])->name('goals.store');
        Route::patch('/goals/{kpiGoal}', [ReportController::class, 'updateGoal'])->name('goals.update');
        Route::delete('/goals/{kpiGoal}', [ReportController::class, 'destroyGoal'])->name('goals.destroy');
        Route::post('/schedule', [ReportController::class, 'storeSchedule'])->name('schedule.store');
        Route::delete('/schedule/{scheduledReport}', [ReportController::class, 'destroySchedule'])->name('schedule.destroy');
    });

    // Review Manager
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/templates', [ReviewController::class, 'templates'])->name('templates');
        Route::get('/trends', [ReviewController::class, 'trends'])->name('trends');
        Route::post('/{review}/generate-reply', [ReviewController::class, 'generateReply'])->name('generateReply');
        Route::post('/{review}/reply', [ReviewController::class, 'saveReply'])->name('saveReply');
        Route::post('/templates', [ReviewController::class, 'storeTemplate'])->name('templates.store');
        Route::delete('/templates/{responseTemplate}', [ReviewController::class, 'destroyTemplate'])->name('templates.destroy');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';
