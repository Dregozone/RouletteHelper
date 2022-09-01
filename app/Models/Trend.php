<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    use HasFactory;
    
    protected $table = 'trends';

    protected $fillable = [
        'type',
        'inARow',
    ];
}
