<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecialDay extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'status', 'date_start', 'date_end', 'student_type'];

    protected $dates = ['date_start', 'date_end'];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'special_day_students');
    }

    public function classes()
    {
        return $this->belongsToMany(GenerationClass::class, 'special_day_classes');
    }
}
