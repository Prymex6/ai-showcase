<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->text('question');
            $table->text('answer');
            $table->string('keywords')->nullable();
            $table->enum('action', ['auto', 'escalate', 'draft'])->default('auto');
            $table->unsignedTinyInteger('confidence')->default(80);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
