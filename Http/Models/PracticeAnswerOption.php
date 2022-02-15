<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeAnswerOption extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_answer_options';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_question_id ', 'option_text', 'right_answer', 'type'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceQuestions(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class);
    }
}
