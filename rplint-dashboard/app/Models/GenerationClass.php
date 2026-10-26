<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerationClass extends Model
{
    use HasFactory;
    protected $fillable = ['generation_name', 'class_name', 'class_teacher', 'homeroom_teacher_id'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function specialDays()
    {
        return $this->belongsToMany(SpecialDay::class, 'special_day_classes');
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    protected static function booted()
    {
        static::saving(function (self $class) {
            if ($class->homeroom_teacher_id) {
                $class->class_teacher = optional($class->homeroomTeacher)->name
                    ?? User::find($class->homeroom_teacher_id)?->name;
            } else {
                $class->class_teacher = null;
            }
        });
    }
}
