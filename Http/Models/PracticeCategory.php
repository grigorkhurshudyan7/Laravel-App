<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeCategory extends Model
{
    const MATH = 1;
    const VERBAL = 2;
    const INTEGRATED_REASONING = 3;

    protected $table = 'prac_categories';

    protected $fillable = [
        'category_title',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceQuestionType(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeQuestionType::class, 'prac_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceQuestion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeQuestion::class);
    }
}
