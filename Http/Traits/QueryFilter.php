<?php

namespace App\Http\Traits;

use App\Models\PracticeDifficulty;
use App\Models\PracticeQuestion;
use Illuminate\Support\Facades\Auth;

trait QueryFilter
{
    /**
     * @param $request
     * @param $subjects
     * @param $difficulties
     * @return mixed
     */
    public function practiceQuestionGlobalQuery($request, $subjects, $difficulties, $answeredQuestionsId = [])
    {
        if ((count($difficulties) == 1 && $difficulties[0] == PracticeDifficulty::ADAPTIVE) || !count($difficulties)) {
            $difficulties = [PracticeDifficulty::EASY, PracticeDifficulty::MEDIUM, PracticeDifficulty::HARD, PracticeDifficulty::VERY_HARD];
        }

        $questionPool = [];
        $whereHas = 'whereHas';
        switch ($request->question_pool) {
            case 'unanswered':
                $questionPool = [
                    'relationName' => 'practiceUserAnswer',
                    'where' => [
                        'prac_user_answers.prac_user_id' => Auth::id()
                    ]
                ];
                $whereHas = 'whereDoesntHave';
                break;
            case 'incorrect':
                $questionPool = [
                    'relationName' => 'practiceUserAnswer',
                    'where' => [
                        'prac_user_answers.prac_user_id' => Auth::id(),
                        'prac_user_answers.status' => 0
                    ]
                ];
                break;
            case 'flagged':
                $questionPool = [
                    'relationName' => 'practiceUserFlag',
                    'where' => [
                        'prac_user_questions_flaggeds.prac_user_id' => Auth::id(),
                    ]
                ];
                break;
            case 'answered_and_unanswered':
                $questionPool = [
                    'relationName' => 'practiceUserAnswer',
                    'where' => [
                        'prac_user_answers.prac_user_id' => !Auth::id(),
                    ]
                ];
                $whereHas = 'whereDoesntHave';
                break;
        }

        $questionData = PracticeQuestion::whereHas('practiceSubjectQuestionRel', function ($query) use ($subjects) {
            $query->whereIn('prac_subject_question_pivots.prac_subject_id', $subjects);
        })
            ->$whereHas($questionPool['relationName'], function ($query) use ($questionPool) {
                $query->where($questionPool['where']);
            })
            ->whereNotIn('id', $answeredQuestionsId)
            ->with(['practiceAnswerOption', 'explanationImage'])
            ->whereIn('prac_difficulty_id', $difficulties);

        return $questionData;
    }
}
