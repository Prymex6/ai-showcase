<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->unsignedTinyInteger('rating');
            $table->text('content');
            $table->string('platform')->default('google');
            $table->string('sentiment')->nullable();
            $table->text('reply')->nullable();
            $table->enum('status', ['pending', 'replied', 'ignored'])->default('pending');
            $table->timestamp('review_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
