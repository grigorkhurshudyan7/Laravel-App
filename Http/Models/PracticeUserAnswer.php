<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PracticeUserAnswer extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_user_answers';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_user_id', 'prac_answer_option_id', 'prac_question_id', 'prac_question_type_id', 'status', 'pace', 'others_pace', 'status', 'test_name'
    ];

    /**
     * @param $value
     */
    public function setPaceAttribute($value)
    {
        $this->attributes['pace'] = gmdate("H:i:s", $value);
    }

    /**
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value): string
    {
        $date = Carbon::parse($value, 'UTC');
        return $date->isoFormat('MMMM D YYYY');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceQuestions(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class, 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceQuestionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestionType::class, 'prac_question_type_id');
    }

    /**
     * @param $query
     * @param $filters
     */
    public static function filters($query, $filters)
    {
        if (property_exists($filters, 'difficulty')) {
            $query->whereHas('practiceQuestions.practiceDifficulty', function ($qu) use ($filters) {
                $qu->where('id', $filters->difficulty);
            });
        }
        if (property_exists($filters, 'date')) {
            if ($filters->date->day !== 'session') {
                $query->whereDate('created_at', $filters->date->operator, $filters->date->day);
            } else {
                $query->where('test_name', $query->orderBy('id', 'DESC')->first()->test_name);
            }
        }
        if (property_exists($filters, 'status')) {
            $query->where('status', $filters->status);
        }
    }
}