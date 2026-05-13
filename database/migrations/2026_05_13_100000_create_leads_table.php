<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('service')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contact', 'qualified', 'proposal', 'closed_won', 'closed_lost'])->default('new');
            $table->unsignedTinyInteger('score')->default(50);
            $table->string('source')->default('website');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
