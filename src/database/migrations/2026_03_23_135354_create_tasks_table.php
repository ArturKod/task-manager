<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'in_progress', 'blocked', 'done', 'cancelled'])->default('new');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamps();

            // Индексы для фильтрации
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
