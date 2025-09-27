<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerationClass extends Model
{
    use HasFactory;
    protected $fillable = ['generation_name', 'class_name', 'class_teacher'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function specialDays()
    {
        return $this->belongsToMany(SpecialDay::class, 'special_day_classes');
    }

    
}
