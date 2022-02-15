<?php

namespace App\Http\Controllers;

use App\Http\Resources\PracticeReviewResource;
use App\Models\PracticeUserAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PracticeReviewController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAnswers(Request $request): JsonResponse
    {
        $filters = (object)[];
        if ($request->has('filters')) {
            $filters = $this->filtersParamsGenerate(json_decode($request->all()['filters']));
        }

        $getAnswers = PracticeUserAnswer::with('practiceQuestions.practiceUserFlag',
            'practiceQuestions.practiceDifficulty',
            'practiceQuestionType')
            ->where(['prac_user_id' => Auth::id()])
            ->where(function ($query) use ($filters) {
                PracticeUserAnswer::filters($query, $filters);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(PracticeReviewResource::collection($getAnswers));
    }

    /**
     * @return JsonResponse
     */
    public function getSummary(): JsonResponse
    {
        $getSummary = PracticeUserAnswer::where(['prac_user_id' => Auth::id()]);

        if (!$getSummary->count()) {
            return response()->json([], 404);
        }

        $getTotalPace = $getSummary->sum(DB::raw("TIME_TO_SEC(pace)"));
        $totalCount = $getSummary->count();
        $incorrectAnswerCount = $getSummary->where('status', 0)->count();
        $correctAnswerCount = $totalCount - $incorrectAnswerCount;

        // get percent
        $getCorrectAnswerPercent = number_format(($correctAnswerCount / $totalCount * 100), 0);

        $dataSummary = [
            [
                'percent_correct' => ['percent' => "$getCorrectAnswerPercent %", 'qty' => "($correctAnswerCount of $totalCount)"],
                'average_pece' => ['second' => '001 ', 'total' => '(' . gmdate("H:i:s", $getTotalPace) . ' total)'], // test data
                'others_average' => ['second' => '2:01 ', 'total' => '(10:33 total)'] // test data
            ]
        ];

        return response()->json($dataSummary, 200);
    }

    /**
     * @return JsonResponse
     */
    public function filtersParamsGenerate($filters): object
    {
        $params = (object)[];
        if (property_exists($filters, 'date') && !empty($filters->date)) {
            $params->date = (object)[];
            switch ($filters->date) {
                case 'today':
                    $params->date->operator = '=';
                    $params->date->day = Carbon::now()->toDateString();
                    break;
                case '7':
                case '30':
                    $params->date->operator = '>=';
                    $params->date->day = Carbon::now()->subDays((int)$filters->date)->toDateString();
                    break;
                case 'session':
                    $params->date->day = 'session';
                    break;
                default:
                    $params->date->operator = '<=';
                    $params->date->day = Carbon::now()->toDateString();
                    break;
            }
        }
        if (property_exists($filters, 'status') && $filters->status !== '') {
            $params->status = (int)$filters->status;
        }
        if (property_exists($filters, 'difficulty') && $filters->difficulty !== '') {
            $params->difficulty = (int)$filters->difficulty;
        }

        return $params;
    }
}
