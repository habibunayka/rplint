<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialDayStudents extends Model
{
    use HasFactory;

    protected $table = 'special_day_students';

    protected $fillable = ['special_day_id', 'student_id'];
}
