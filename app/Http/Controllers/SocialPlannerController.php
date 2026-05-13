<?php

namespace App\Http\Controllers;

use App\Models\SocialPost;
use App\Models\ContentTemplate;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialPlannerController extends Controller
{
    public function __construct(private OpenAIService $ai) {}

    public function calendar(): Response
    {
        return Inertia::render('Social/Calendar', [
            'posts' => SocialPost::latest('scheduled_at')->get(),
        ]);
    }

    public function templates(): Response
    {
        return Inertia::render('Social/Templates', [
            'templates' => ContentTemplate::latest()->get(),
        ]);
    }

    public function analytics(): Response
    {
        $posts = SocialPost::all();
        return Inertia::render('Social/Analytics', [
            'stats' => [
                'total'       => $posts->count(),
                'by_status'   => $posts->groupBy('status')->map->count()->toArray(),
                'by_platform' => $this->platformCounts($posts),
            ],
        ]);
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content'      => 'required|string',
            'platform'     => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);

        SocialPost::create([
            'content'      => $validated['content'],
            'platforms'    => $validated['platform'] ?? [],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status'       => 'scheduled',
        ]);

        return back()->with('success', 'Post zaplanowany.');
    }

    public function destroyPost(SocialPost $socialPost)
    {
        $socialPost->delete();
        return back()->with('success', 'Post usunięty.');
    }

    public function generateContent(Request $request)
    {
        $platforms = $request->input('platforms', []);
        $platform  = is_array($platforms) ? implode(', ', $platforms) : ($platforms ?: 'social media');
        $topic     = $request->input('topic', 'ogólny post');
        $content   = $this->ai->generatePost($topic, $platform);
        return response()->json(['content' => $content]);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'content'   => 'required|string',
            'platforms' => 'nullable|array',
        ]);

        ContentTemplate::create([
            'name'    => $validated['name'],
            'content' => $validated['content'],
            'type'    => 'other',
        ]);
        return back()->with('success', 'Szablon zapisany.');
    }

    public function destroyTemplate(ContentTemplate $contentTemplate)
    {
        $contentTemplate->delete();
        return back()->with('success', 'Szablon usunięty.');
    }

    private function platformCounts($posts): array
    {
        $counts = [];
        foreach ($posts as $post) {
            foreach ($post->platforms as $platform) {
                $counts[$platform] = ($counts[$platform] ?? 0) + 1;
            }
        }
        return $counts;
    }
}
