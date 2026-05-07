<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PossibleOutcome extends Model
{
    use HasFactory;

    protected $table = 'possible_outcomes';

    protected $fillable = [
        'num',
        'isEven',
        'isOdd',
        'isLow',
        'isHigh',
        'isRed',
        'isBlack',
    ];
}
