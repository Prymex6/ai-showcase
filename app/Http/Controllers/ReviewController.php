<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ResponseTemplate;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function __construct(private OpenAIService $ai) {}

    public function index(): Response
    {
        return Inertia::render('Reviews/Index', [
            'reviews'   => Review::latest('review_date')->get(),
            'pending'   => Review::where('status', 'pending')->count(),
            'avgRating' => round(Review::avg('rating'), 1),
        ]);
    }

    public function templates(): Response
    {
        return Inertia::render('Reviews/Templates', [
            'templates' => ResponseTemplate::orderBy('min_rating')->get(),
        ]);
    }

    public function trends(): Response
    {
        $reviews = Review::all();
        $total   = $reviews->count();
        return Inertia::render('Reviews/Trends', [
            'stats' => [
                'total'      => $total,
                'avg_rating' => round($reviews->avg('rating'), 1),
                'sentiment'  => [
                    'positive' => $reviews->where('sentiment', 'positive')->count(),
                    'neutral'  => $reviews->where('sentiment', 'neutral')->count(),
                    'negative' => $reviews->where('sentiment', 'negative')->count(),
                ],
                'by_rating'  => $reviews->groupBy('rating')->map->count()->toArray(),
                'reply_rate' => $total > 0 ? round($reviews->whereNotNull('reply')->count() / $total * 100) : 0,
            ],
        ]);
    }

    public function generateReply(Review $review)
    {
        $reply = $this->ai->replyToReview($review->content, $review->rating);
        return response()->json(['reply' => $reply]);
    }

    public function saveReply(Request $request, Review $review)
    {
        $request->validate(['reply' => 'required|string']);
        $review->update(['reply' => $request->reply, 'status' => 'replied']);
        return back()->with('success', 'Odpowiedź zapisana.');
    }

    public function storeTemplate(Request $request)
    {
        ResponseTemplate::create($request->validate([
            'name'           => 'required|string|max:255',
            'sentiment_type' => 'nullable|in:positive,neutral,negative',
            'min_rating'     => 'required|integer|min:1|max:5',
            'max_rating'     => 'required|integer|min:1|max:5',
            'content'        => 'required|string',
        ]));
        return back()->with('success', 'Szablon dodany.');
    }

    public function destroyTemplate(ResponseTemplate $responseTemplate)
    {
        $responseTemplate->delete();
        return back()->with('success', 'Szablon usunięty.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Opinia usunięta.');
    }
}
