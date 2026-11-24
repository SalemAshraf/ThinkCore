<?php

namespace App\Support;

use App\Models\User;

class InstructorReviewSnapshot
{
    public static function token(User $user): string
    {
        return hash('sha256', json_encode([$user->id, $user->name, $user->email, $user->document, $user->role, $user->approved_status, $user->updated_at?->toISOString()], JSON_THROW_ON_ERROR));
    }
}
