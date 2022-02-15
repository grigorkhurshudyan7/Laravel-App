<?php

namespace App\Http\Controllers;

use App\Http\Traits\DataFilter;
use App\Http\Traits\QueryFilter;
use App\Models\Lessons;
use App\Models\PracticeCategory;
use App\Models\PracticeDifficulty;
use App\Models\PracticeQuestion;
use App\Models\PracticeQuestionType;
use App\Models\PracticeSubject;
use App\Models\PracticeUserFlag;
use App\Models\PracticeUserNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TestPracticeController extends Controller
{
    use DataFilter, QueryFilter;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function practiceStore(Request $request)
    {
        Session::forget('questions_id_array');
        Session::forget('quiz_option');
        Session::forget('alreadySelectedQuestions');
        Session::forget('test_name');
        Session::forget('practiceAnswerOption');
        Session::put('quiz_option', $request->all());

        $pracSessionData = Session::get('quiz_option');
        $difficultyArray = $pracSessionData['difficulty'];
        $subjectsArray = $pracSessionData['selected_subjects'];

        /** START GET GLOBAL QUERY **/
        $filterQuery = $this->practiceQuestionGlobalQuery($request, $subjectsArray, $difficultyArray);
        $count = ($request->question_number === PracticeQuestion::UNLIMITED || $request->question_number === 0) ? null : intval($request->question_number);
        $questionCount = $filterQuery->limit($count)->count();
        $question = $filterQuery->orderByRaw('RAND()')->limit(1)->first();

        if (!$questionCount) {
            return response([
                'data' => "You don't have any flagged questions with these options."
            ], 400);
        }
        /** END GET GLOBAL QUERY **/

        /** START GET QUESTION FLAG **/
        $getQuestionFlag = PracticeUserFlag::where([
            'prac_user_id' => Auth::id(),
            'prac_question_id' => $question->id,
        ])->first();
        /** END GET QUESTION FLAG **/
        /** START GET QUESTION NOTE **/
        $getNote = PracticeUserNote::where([
            'prac_user_id' => Auth::id(),
            'prac_question_id' => $question->id,
        ])->first('note');
        /** END GET QUESTION NOTE **/

        if (isset($question->practiceAnswerOption)) {
            Session::put('practiceAnswerOption', $question->practiceAnswerOption);
        }

        $answeredQuestions = [];
        array_push($answeredQuestions, $question->id);
        Session::put('question_id', $answeredQuestions);
        Session::put('single_question_id', $question->id);

        return response([
            'data' => $question,
            'userNote' => $getNote->note ?? null,
            'flag' => $getQuestionFlag ?: false,
            'count' => $questionCount,
            'mode' => $pracSessionData['mode'],
        ], 200);
    }

    /**
     * @param false $param
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getDifficulties($param = false)
    {
        $getDifficulties = PracticeDifficulty::whereNotIn('id', [$param ? PracticeDifficulty::ADAPTIVE : ''])->get();

        return response($getDifficulties, 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPracticeCategory(): \Illuminate\Http\JsonResponse
    {
        $getQuestionTypesWithSubjects = PracticeCategory::with(['practiceQuestionType' => function ($query) {
            $query->with(['practiceSubjectQuestionTypeRel' => function ($query) {
                $query->where('prac_subject_question_types.prac_question_type_id', PracticeQuestionType::ALL_MATH);
            }])
                ->where('prac_question_types.prac_category_id', PracticeCategory::MATH);
        }])->get();

        return response()->json($getQuestionTypesWithSubjects, 200);
    }

    /**
     * @param $categoryId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getQuestionTypes($categoryId)
    {
        $getQuestionTypesWithSubjects = PracticeQuestionType::with('practiceSubjectQuestionTypeRel')
            ->where('prac_category_id', $categoryId)
            ->get();

        $filteredData = $this->dataKeyFilter($getQuestionTypesWithSubjects, true, 'practiceSubjectQuestionTypeRel');
        return response($filteredData, 200);
    }

    /**
     * @param $questionTypeID
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getSubjects($questionTypeID)
    {
        $getQuestionTypesWithSubjects = PracticeSubject::whereHas('practiceSubjectQuestionTypeRel', function ($query) use ($questionTypeID) {
            $query->where('prac_question_type_id', $questionTypeID);
        })->get();

        return response($getQuestionTypesWithSubjects, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getQuestionsCount(Request $request)
    {
        $filterQuery = $this->practiceQuestionGlobalQuery($request, $request->selected_subjects, $request->difficulty);
        $count = ($request->question_number === PracticeQuestion::UNLIMITED || $request->question_number === 0) ? null : intval($request->question_number);
        $getQuestionCount = $filterQuery->limit($count)->get();
        return response(['count' => $getQuestionCount->count()], 200);
    }

    /**
     * @param int $questionId
     * @return JsonResponse
     */
    public function getQuestionLessons(int $questionId): JsonResponse
    {
        $less = Lessons::select(['id','name'])->whereHas('practiceQuestionLessonRel', function ($query) use($questionId) {
            $query->where('prac_question_lesson_pivots.question_id', $questionId);
        })->get();

        if (!count($less)) $less = [];

        return response()->json($less);
    }
}
