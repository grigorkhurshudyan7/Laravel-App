<?php

namespace App\Http\Controllers;

use App\Http\Requests\PracticeUserAnswerStoreRequest;
use App\Http\Traits\DataFilter;
use App\Http\Traits\QueryFilter;
use App\Models\Lessons;
use App\Models\PracticeQuestion;
use App\Models\PracticeUserAnswer;
use App\Models\PracticeUserFlag;
use App\Models\PracticeUserNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PracticeUserAnswerController extends Controller
{
    use DataFilter, QueryFilter;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function answerStore(PracticeUserAnswerStoreRequest $request)
    {
        /** GENERATE  TEST NAME **/
        if (!Session::get('test_name')) {

            $testName = 'TN-' . Auth::id() . '-' . time();
            Session::put('test_name', $testName);
        }

        $pracSessionData = Session::get('quiz_option');

        if (!Session::get('questions_id_array')) {
            $answeredQuestions_id = Session::get('question_id');
        } else {
            $answeredQuestions_id = Session::get('questions_id_array');
        }

        /** START CHECK ANSWER IS CORRECT **/
        $practiceAnswerOption = Session::get('practiceAnswerOption');
        $answerIsCorrect = 0;
        if ($practiceAnswerOption) {
            foreach ($practiceAnswerOption as $options) {
                if ($options->id === $request->answer) {
                    $answerIsCorrect = $options->right_answer;
                    break;
                }
            }
        }
        Session::forget('practiceAnswerOption');
        /** END CHECK ANSWER IS CORRECT **/
        $currentQuestionId = Session::get('single_question_id');
        /** START SAVE USER ANSWER **/
        $answerData = [
            'prac_user_id' => Auth::id(),
            'prac_answer_option_id' => $request->answer,
            'prac_question_id' => $currentQuestionId,
            'prac_question_type_id' => $pracSessionData['question_type'],
            'pace' => $request->pace,
            'status' => $answerIsCorrect,
            'test_name' => Session::get('test_name'),
        ];

        $createAnswer = PracticeUserAnswer::create($answerData);
        /** END SAVE USER ANSWER **/
        /** START CONSTRUCT ANSWER DATA **/
        $getQuestionDifficultyName = PracticeQuestion::with('practiceDifficulty:id,difficulty_title')
            ->where('id', $currentQuestionId)
            ->firstOrFail();

        $getQuestionLessons = Lessons::whereHas('practiceQuestionLessonRel', function ($query) use ($currentQuestionId) {
            $query->where('prac_question_lesson_pivots.prac_question_id', $currentQuestionId);
        })->select('id', 'name', 'length')->get();
        /** END CONSTRUCT ANSWER DATA **/

        if ($pracSessionData['mode'] == PracticeQuestion::QUIZ_MODE) {
            /** START GET NEW QUESTION **/
            $difficulties = $pracSessionData['difficulty'];
            $subjectsArray = $pracSessionData['selected_subjects'];
            $filterQuery = $this->practiceQuestionGlobalQuery((object)$pracSessionData, $subjectsArray, $difficulties, $answeredQuestions_id);
            $question = $filterQuery->orderByRaw('RAND()')->limit(1)->first();
            /** END GET NEW QUESTION **/
            if (isset($question->practiceAnswerOption)) {
                Session::put('practiceAnswerOption', $question->practiceAnswerOption);
            }
            if ($question) {

                $getQuestionFlag = PracticeUserFlag::where([
                    'prac_user_id' => Auth::id(),
                    'prac_question_id' => $question->id,
                ])->first();

                $getNote = PracticeUserNote::where([
                    'prac_user_id' => Auth::id(),
                    'prac_question_id' => $question->id,
                ])->first('note');

                array_push($answeredQuestions_id, $question->id);
                Session::put('single_question_id', $question->id);
                Session::put('questions_id_array', $answeredQuestions_id);

                return response(
                    [
                        'data' => $question,
                        'userNote' => $getNote->note ?? null,
                        'flag' => $getQuestionFlag ? true : false,
                    ], 200);
            }
        }

        $answerResult = [
            [
                'Title' => $getQuestionDifficultyName->question_title,
                'Your Results' => $answerIsCorrect ? 'Correct' : 'Incorrect',
                'Difficulty' => $getQuestionDifficultyName->practiceDifficulty->difficulty_title ?? '',
                'Your Pace' => gmdate("H:i:s", $request->pace),
                'Others’ Pace' => 0,
            ]
        ];

        return response([
            'data' => [],
            'test_name' => Session::get('test_name'),
            'lessons' => $getQuestionLessons,
            'answerResult' => $answerResult,
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function nextQuestion(Request $request)
    {
        Session::forget('practiceAnswerOption');

        $pracSessionData = Session::get('quiz_option');

        if (!Session::get('questions_id_array')) {
            $answeredQuestions_id = Session::get('question_id');
        } else {
            $answeredQuestions_id = Session::get('questions_id_array');
        }

        /** START GET NEW QUESTION **/
        $difficulties = $pracSessionData['difficulty'];
        $subjectsArray = $pracSessionData['selected_subjects'];
        $filterQuery = $this->practiceQuestionGlobalQuery((object)$pracSessionData, $subjectsArray, $difficulties, $answeredQuestions_id);
        $question = $filterQuery->orderByRaw('RAND()')->limit(1)->first();
        /** END GET NEW QUESTION **/
        if ($question) {

            $currentQuestionId = Session::get('single_question_id');
            $getQuestionLessons = Lessons::whereHas('practiceQuestionLessonRel', function ($query) use ($currentQuestionId) {
                $query->where('prac_question_lesson_pivots.prac_question_id', $currentQuestionId);
            })->select('id', 'name', 'length')->get();

            $getQuestionFlag = PracticeUserFlag::where([
                'prac_user_id' => Auth::id(),
                'prac_question_id' => $question->id,
            ])->first();

            $getNote = PracticeUserNote::where([
                'prac_user_id' => Auth::id(),
                'prac_question_id' => $question->id,
            ])->first('note');

            if (isset($question->practiceAnswerOption)) {
                Session::put('practiceAnswerOption', $question->practiceAnswerOption);
            }

            array_push($answeredQuestions_id, $question->id);
            Session::put('single_question_id', $question->id);
            Session::put('questions_id_array', $answeredQuestions_id);

            return response(
                [
                    'data' => $question,
                    'userNote' => $getNote->note ?? null,
                    'lessons' => $getQuestionLessons,
                    'flag' => $getQuestionFlag ? true : false,
                ], 200);
        }

        Session::forget('questions_id_array');
        return response([
            'data' => [],
            'test_name' => Session::get('test_name'),
        ], 200);

    }

    /**
     * @param $testName
     */
    public function getLastAnswerQuestions($testName)
    {
        $getLastAnswer = PracticeUserAnswer::where(['test_name' => $testName, 'prac_user_id' => Auth::id()])->get();
        return response($getLastAnswer, 200);
    }

    /**
     * @param Request $request
     */
    public function questionFlag(Request $request)
    {
        if (!$request->flag) {
            PracticeUserFlag::create(['prac_user_id' => Auth::id(), 'prac_question_id' => $request->question_id]);
            $flagQuestion = true;
        } else {
            PracticeUserFlag::where(['prac_user_id' => Auth::id(), 'prac_question_id' => $request->question_id])->delete();
            $flagQuestion = false;
        }
        return response(['flag' => $flagQuestion], 200);
    }

}
