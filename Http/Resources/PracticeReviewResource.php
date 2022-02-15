<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PracticeReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'result'         => $this->status,
            'question_title' => $this->practiceQuestions->question_title,
            'section'        => $this->practiceQuestionType->question_title,
            'difficulty'     => $this->practiceQuestions->practiceDifficulty->difficulty_title,
            'your_pace'      => $this->pace,
            'others_pace'    => '0:00',
            'date'           => $this->created_at,
            'flag'           => count($this->practiceQuestions->practiceUserFlag) ?: false,
        ];
    }
}
