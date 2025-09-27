<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'status', 'check_in_time', 'check_out_time', 'updated_at'];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function generationClass()
    {
        return $this->hasOneThrough(
            GenerationClass::class,
            Student::class,
            'id', // Foreign key on Student
            'id', // Foreign key on GenerationClass
            'student_id', // Local key on Attendance
            'generation_class_id' // Local key on Student
        );
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->updated_at = now();
        });

        static::updating(function ($model) {
            $model->updated_at = now();
        });
    }
}
