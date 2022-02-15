<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeUserFlag extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_user_questions_flaggeds';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_user_id', 'prac_question_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceQuestions(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class);
    }
}