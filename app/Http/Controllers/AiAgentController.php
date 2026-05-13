<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\Conversation;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AiAgentController extends Controller
{
    public function __construct(private OpenAIService $ai) {}

    public function chat(): Response
    {
        return Inertia::render('Agent/Chat', [
            'settings' => [
                'model'          => config('services.openai.model'),
                'demo_mode'      => config('services.openai.demo_mode'),
                'kb_count'       => KnowledgeBase::where('is_active', true)->count(),
                'api_configured' => $this->ai->isConfigured(),
            ],
        ]);
    }

    public function knowledgeBase(): Response
    {
        return Inertia::render('Agent/KnowledgeBase', [
            'entries' => KnowledgeBase::latest()->get(),
        ]);
    }

    public function history(): Response
    {
        $sessions = Conversation::select('session_id')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('MAX(created_at) as last_at')
            ->groupBy('session_id')
            ->orderByDesc('last_at')
            ->get();

        return Inertia::render('Agent/History', [
            'sessions' => $sessions,
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Agent/Settings', [
            'settings' => [
                'model'          => config('services.openai.model'),
                'demo_mode'      => config('services.openai.demo_mode'),
                'kb_count'       => KnowledgeBase::where('is_active', true)->count(),
                'api_configured' => $this->ai->isConfigured(),
            ],
        ]);
    }

    public function historySession(string $sessionId)
    {
        $messages = Conversation::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['id', 'role', 'message as content', 'created_at']);
        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $sessionId = $request->session()->get('chat_session_id', Str::uuid()->toString());
        $request->session()->put('chat_session_id', $sessionId);

        Conversation::create([
            'session_id' => $sessionId,
            'role'       => 'user',
            'message'    => $request->message,
        ]);

        $kb = KnowledgeBase::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('question', 'like', '%' . $request->message . '%')
                  ->orWhere('keywords', 'like', '%' . $request->message . '%');
            })
            ->first();

        $context = $kb ? "Q: {$kb->question}\nA: {$kb->answer}" : '';
        $result  = $this->ai->customerServiceReply($request->message, $context);

        if ($kb) {
            $kb->increment('usage_count');
        }

        $conv = Conversation::create([
            'session_id' => $sessionId,
            'role'       => 'assistant',
            'message'    => $result['answer'],
            'action'     => $result['action'],
            'confidence' => $result['confidence'],
        ]);

        return response()->json([
            'reply'      => $result['answer'],
            'action'     => $result['action'],
            'confidence' => $result['confidence'],
        ]);
    }

    public function storeKbEntry(Request $request)
    {
        KnowledgeBase::create($request->validate([
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'category'   => 'nullable|string|max:100',
            'keywords'   => 'nullable|string|max:255',
        ]));
        return back()->with('success', 'Wpis dodany do bazy wiedzy.');
    }

    public function updateKbEntry(Request $request, KnowledgeBase $knowledgeBase)
    {
        $knowledgeBase->update($request->validate([
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'category'   => 'nullable|string|max:100',
            'keywords'   => 'nullable|string|max:255',
        ]));
        return back()->with('success', 'Wpis zaktualizowany.');
    }

    public function destroyKbEntry(KnowledgeBase $knowledgeBase)
    {
        $knowledgeBase->delete();
        return back()->with('success', 'Wpis usunięty.');
    }
}
