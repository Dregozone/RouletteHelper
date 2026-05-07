<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opposite extends Model
{
    use HasFactory;

    protected $table = 'opposites';

    protected $fillable = [
        'type',
        'opposite',
    ];
}
