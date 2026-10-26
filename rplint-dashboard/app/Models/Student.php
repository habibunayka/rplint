<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'generation_class_id',
        'absence',
        'nis',
        'nisn',
        'profile_photo',
        'address',
        'phone_number',
        'fingerprint_template1',
        'fingerprint_template2',
        'fingerprint_template3',
        'user_id',
    ];

    public function generationClass()
    {
        return $this->belongsTo(GenerationClass::class, 'generation_class_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo ? Storage::url($this->profile_photo) : null;
    }

    public function specialDays()
    {
        return $this->belongsToMany(SpecialDay::class, 'special_day_students');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::deleting(function ($student) {
            if ($student->profile_photo) {
                Storage::disk('public')->delete($student->profile_photo);
            }
        });

        static::updating(function ($student) {
            if ($student->isDirty('profile_photo')) {
                if ($student->getOriginal('profile_photo')) {
                    Storage::disk('public')->delete($student->getOriginal('profile_photo'));
                }
            }
        });
    }
}
