<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PracticeReviewSummaryResource extends JsonResource
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
            [
                'average_pece' =>
                    [
                        'second' => '001 ', //test data
                        'total' => ' (0:09 total)',  //test data
                    ]
            ],
        ];
    }
}
