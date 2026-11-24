<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained('course_sections')->nullOnDelete();
        });
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->string('status')->default('pending');
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
        Schema::dropIfExists('course_sections');
    }
};
