<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_lesson_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_lessons');
    }
};
