<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PracticeExplanationImage extends Model
{
    /**
     * @var string
     */
    protected $table = 'prac_explanation_images';

    /**
     * @var string[]
     */
    protected $fillable = [
        'prac_question_id', 'image'
    ];

    /**
     * @param $request
     * @param $question
     */
    public static function explanationImage($request, $question)
    {
        if (isset($request->deletedExplanationImage) && $request->deletedExplanationImage) {
            self::destroy(collect([$request->deletedExplanationImage]));
        }

        $filterExplanationImages = [];
        if($request->explanation_images) {
            foreach ($request->explanation_images as $explanationImage) {
                $explanationImageName = Str::random(10) . '.' . $explanationImage['image']->getClientOriginalExtension();
                $filterExplanationImages[]['image'] = $explanationImageName;

                Storage::disk('public')->putFileAs(
                    '/images',
                    $explanationImage['image'],
                    $explanationImageName
                );
            }
        }
        $question->explanationImage()->createMany($filterExplanationImages);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practiceQuestion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class);
    }
}
