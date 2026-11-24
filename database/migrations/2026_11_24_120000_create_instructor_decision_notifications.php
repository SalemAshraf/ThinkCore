<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instructor_decision_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('decision_key',64)->unique();
            $table->string('recipient');
            $table->string('recipient_name');
            $table->string('decision',16);
            $table->timestamp('decided_at');
            $table->string('status',16)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->uuid('claim_token')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('instructor_decision_notifications'); }
};
