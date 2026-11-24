<?php

namespace App\Support;

use App\Models\CourseCategory;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;
use Illuminate\Validation\ValidationException;

/** Active writers must hold these rows until their transaction commits. */
final class TaxonomyReferences
{
    public static function lock(array $data): void
    {
        foreach (['category' => CourseCategory::class, 'level' => CourseLevel::class, 'language' => CourseLanguage::class] as $field => $model) {
            if (empty($data[$field])) continue;
            $item = $model::whereKey($data[$field])->lockForUpdate()->first();
            if (!$item || ($field === 'category' && !$item->status)) {
                throw ValidationException::withMessages([$field => 'The selected item is no longer available.']);
            }
        }
    }
}
