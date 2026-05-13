<?php

namespace Tests\Unit;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeReview(int $rating): Review
    {
        return Review::create([
            'author_name' => 'Jan Kowalski',
            'rating'      => $rating,
            'content'     => 'Treść opinii',
            'platform'    => 'google',
            'sentiment'   => $rating >= 4 ? 'positive' : ($rating === 3 ? 'neutral' : 'negative'),
            'status'      => 'pending',
            'review_date' => now(),
        ]);
    }

    public function test_high_rating_stored_as_positive_sentiment(): void
    {
        $review = $this->makeReview(5);
        $this->assertEquals('positive', $review->sentiment);
    }

    public function test_four_star_stored_as_positive(): void
    {
        $review = $this->makeReview(4);
        $this->assertEquals('positive', $review->sentiment);
    }

    public function test_three_star_stored_as_neutral(): void
    {
        $review = $this->makeReview(3);
        $this->assertEquals('neutral', $review->sentiment);
    }

    public function test_two_star_stored_as_negative(): void
    {
        $review = $this->makeReview(2);
        $this->assertEquals('negative', $review->sentiment);
    }

    public function test_one_star_stored_as_negative(): void
    {
        $review = $this->makeReview(1);
        $this->assertEquals('negative', $review->sentiment);
    }

    public function test_reply_starts_null(): void
    {
        $review = $this->makeReview(5);
        $this->assertNull($review->reply);
        $this->assertEquals('pending', $review->status);
    }

    public function test_saving_reply_updates_status(): void
    {
        $review = $this->makeReview(4);
        $review->update(['reply' => 'Dziękujemy!', 'status' => 'replied']);

        $this->assertEquals('replied', $review->fresh()->status);
        $this->assertEquals('Dziękujemy!', $review->fresh()->reply);
    }
}
