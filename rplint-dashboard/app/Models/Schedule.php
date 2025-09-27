<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'generation_class_id', 'teacher', 'subject', 'day', 'type', 'start_time', 'end_time'
    ];


    public function generationClass()
    {
        return $this->belongsTo(GenerationClass::class, 'generation_class_id');
    }
}
