<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeUserNote extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_user_question_notes';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_user_id', 'prac_question_id', 'note'
    ];
}