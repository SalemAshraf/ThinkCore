<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLanguage extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
