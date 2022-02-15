<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeDifficulty extends Model
{
    const ADAPTIVE = 1;
    const EASY = 2;
    const MEDIUM = 3;
    const HARD = 4;
    const VERY_HARD = 5;

    /**
     * @var string
     */
    protected $table = 'prac_difficulties';

    /**
     * @var string[]
     */
    protected $fillable = [
        'difficulty_title',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceQuestion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeQuestion::class);
    }
}
