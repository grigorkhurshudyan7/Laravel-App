<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api']], function () {
    /** START PRACTICE TEST PAGE */
    Route::get('/get-practice-category','TestPracticeController@getPracticeCategory');
    Route::get('/get-difficulties/{param?}','TestPracticeController@getDifficulties');
    Route::get('/get-question-types/{id}','TestPracticeController@getQuestionTypes');
    Route::get('/get-subjects/{id}','TestPracticeController@getSubjects');
    Route::post('/practice-store','TestPracticeController@practiceStore');
    Route::post('/get-questions-count','TestPracticeController@getQuestionsCount');
    Route::get('/get-lessons/{question_id}','TestPracticeController@getQuestionLessons');
    /** END PRACTICE TEST PAGE */
    /**----------------------**/
    /** START Lessons PAGE **/
    Route::get('/lessons/list', 'LessonsController@list');
    /** END Lessons PAGE */
    /**----------------------**/
    /** START QUESTION PAGE **/
    Route::post('/answer-store','PracticeUserAnswerController@answerStore');
    Route::post('/next-question','PracticeUserAnswerController@nextQuestion');
    Route::post('/question-flag','PracticeUserAnswerController@questionFlag');
    Route::post('/update-notes','PracticeUserAnswerController@updateAnswerNotes');
    Route::get('/get-last-answer-questions/{test_name}','PracticeUserAnswerController@getLastAnswerQuestions');
    /** END QUESTION PAGE **/
    /**------------------**/
    /** START QUESTION NOTES **/
    Route::post('/user-note-store','PracticeQuestionNoteController@saveNote');
    /** END QUESTIONS NOTES */
    /**----------------------**/
    /** START REVIEWS  */
    Route::group(['prefix' => '/answers'], function () {
        Route::get('/get-answers', 'PracticeReviewController@getAnswers');
        Route::get('/get-summary', 'PracticeReviewController@getSummary');
    });
    /** END REVIEWS */
});
