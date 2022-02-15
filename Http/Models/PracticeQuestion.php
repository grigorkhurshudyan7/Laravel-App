<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PracticeQuestion extends Model
{

    const UNLIMITED = 'unlimited';

    const PRACTICE_MODE = 'practice_mode';

    const QUIZ_MODE = 'quiz_mode';

    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'prac_questions';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_difficulty_id', 'prac_category_id', 'prac_question_type_id', 'question_content', 'question_title', 'text_explanation', 'image'
    ];

    public function getCreatedAtAttribute($value): string
    {
        $date = Carbon::parse($value, 'UTC');
        return $date->isoFormat('MMMM D YYYY');
    }

    /**
     * @param $request
     */
    public static function uploadQuestionImage($request, $question, $action)
    {
        if ($request->file('file')) {
            $imageName = time() . '.' . $request->file('file')->getClientOriginalExtension();

            if ($action === 'edit' && (isset($question->image) && $question->image !== null)) {
                Storage::disk('public')->delete('/images/' . $question->image);
            }

            Storage::disk('public')->putFileAs(
                '/images',
                $request->file('file'),
                $imageName
            );

            $request->request->add(['image' => $imageName]);
        }

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceDifficulty(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeDifficulty::class, 'prac_difficulty_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeCategory::class, 'prac_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function questionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestionType::class, 'prac_question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceAnswerOption(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeAnswerOption::class, 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceUserAnswer(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeUserAnswer::class, 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practiceUserFlag(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeUserFlag::class, 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceSubjectQuestionRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PracticeSubject::class, 'prac_subject_question_pivots', 'prac_question_id', 'prac_subject_id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceQuestionNoteRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prac_user_question_notes', 'prac_user_id', 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function explanationImage(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PracticeExplanationImage::class, 'prac_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function practiceQuestionLessonRel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Lessons::class, 'prac_question_lesson_pivots', 'prac_question_id', 'lesson_id')->withTimestamps();
    }
}