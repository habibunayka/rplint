<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialDayClass extends Model
{
    use HasFactory;

    protected $table = 'special_day_classes';

    protected $fillable = ['generation_class_id', 'special_day_id'];
}
