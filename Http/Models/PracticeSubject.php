<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeSubject extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_subjects';

    /**
     * @var string[]
     */
    protected $fillable = [
        'subject_title'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceSubjectQuestionTypeRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PracticeQuestionType::class, 'prac_subject_question_types', 'prac_subject_id', 'prac_question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceSubjectQuestionRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PracticeQuestion::class, 'prac_subject_question_pivots', 'prac_subject_id', 'prac_question_id')->withTimestamps();
    }
}