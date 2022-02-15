<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeQuestionType extends Model
{
    const ALL_MATH = 1;
    const DATA_SUFFICIENCY = 2;
    const PROBLEM_SOLVING = 3;
    const ALL_VERBAL = 4;
    const SENTENCE_CORRECTION = 5;
    const CRITICAL_REASONING = 6;
    const READING_COMPREHENSION = 7;
    const ALL_INTEGRATED_REASONING = 8;

    /**
     * @var string
     */
    protected $table = 'prac_question_types';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_category_id ', 'question_title',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceCategory() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsTo(PracticeCategory::class, 'prac_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceSubjectQuestionTypeRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PracticeSubject::class, 'prac_subject_question_types', 'prac_question_type_id', 'prac_subject_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceUserAnswer(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeUserAnswer::class, 'prac_question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceQuestion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeQuestion::class, 'prac_question_type_id');
    }
}
