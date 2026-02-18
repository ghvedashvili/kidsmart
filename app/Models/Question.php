<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'type',
        'question',
        'rules',
        'answer',
        'hints', // დაამატე hints აქ
    ];

    // აქ ვეტყვით Laravel-ს რომ JSON ველები უნდა გადაიყვანოს array–ად
    protected $casts = [
        'answer' => 'array',
        'hints' => 'array',
    ];
}
