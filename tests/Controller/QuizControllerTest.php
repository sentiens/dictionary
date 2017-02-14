<?php

namespace Tests\AppBundle\Controller;

use AppBundle\App\AnswerQuestion;
use AppBundle\App\AskNewQuestion;
use AppBundle\App\BeginQuiz;
use AppBundle\App\GetQuiz;
use AppBundle\App\Exception as AppExceptions;
use AppBundle\Controller\QuizController;
use AppBundle\DTO\Quiz as QuizDTO;
use AppBundle\Entity\QuizStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class QuizControllerTest extends \PHPUnit_Framework_TestCase {

    public function test_beginAction_returns_invalid_response_if_no_name_provided()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('name')->willReturn(null);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->beginAction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_JUST_ERROR, $this->getResponseContent($response)->code);
    }

    public function test_beginAction_returns_invalid_response_if_name_is_not_string()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('name')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->beginAction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_JUST_ERROR, $this->getResponseContent($response)->code);
    }

    public function test_beginAction_returns_invalid_response_if_name_invalid()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $beginQuiz->expects($this->once())->method('execute')->with('(*&#')->willThrowException(
            new AppExceptions\InvalidUsernameException()
        );
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('name')->willReturn('(*&#');

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->beginAction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_JUST_ERROR, $this->getResponseContent($response)->code);
    }

    public function test_beginAction_returns_id_of_created_quiz()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $beginQuiz->expects($this->once())->method('execute')->with('Morpheus')->willReturn(9);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('name')->willReturn('Morpheus');

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->beginAction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(9, $this->getResponseContent($response)->data->id);
    }

    public function test_getAction_returns_404()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $getQuiz->expects($this->once())->method('execute')->with(10)->willThrowException(new AppExceptions\QuizNotFoundException);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->getAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_JUST_ERROR, $this->getResponseContent($response)->code);
    }

    public function test_getAction_returns_dto()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $getQuiz->expects($this->once())->method('execute')->with(10)->willReturn(
            new QuizDTO(
                10,
                QuizStatus::NoQuestion()->toNative(),
                5,
                'Vasiliy',
                3
            )
        );
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->getAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(10, json_decode($response->getContent())->data->id);
    }

    public function test_askAction_returns_ok()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $askNewQuestion->expects($this->once())->method('execute')->with(10);
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->askAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
    }

    public function test_askAction_returns_404()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $askNewQuestion->expects($this->once())->method('execute')->with(10)->willThrowException(new AppExceptions\QuizNotFoundException());
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->askAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_askAction_returns_quiz_is_ended_error()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $askNewQuestion->expects($this->once())->method('execute')->with(10)->willThrowException(new AppExceptions\QuizIsEndedException());
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->askAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_QUIZ_IS_ENDED, json_decode($response->getContent())->code);
    }

    public function test_askAction_returns_already_has_a_question_error()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $askNewQuestion->expects($this->once())->method('execute')->with(10)->willThrowException(new AppExceptions\QuizAlreadyHasAQuestionException());
        $answerQuestion = $this->createMock(AnswerQuestion::class);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->askAction(10);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_HAS_UNANSWERED_QUESTION, json_decode($response->getContent())->code);
    }

    public function test_answerAction_return_invalid_when_no_wordId_provided()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(null);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_answerAction_return_invalid_if_wordId_is_not_integer()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn('adsf');

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_answerAction_return_404_if_quiz_not_found()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $answerQuestion->expects($this->once())->method('execute')->willThrowException(new AppExceptions\QuizNotFoundException());
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_answerAction_return_404_if_word_not_found()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $answerQuestion->expects($this->once())->method('execute')->willThrowException(new AppExceptions\WordNotFoundException());
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_answerAction_return_ok()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $answerQuestion->expects($this->once())->method('execute');
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
    }

    public function test_answerAction_return_wrong_answer()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $answerQuestion->expects($this->once())->method('execute')->willThrowException(new AppExceptions\WrongAnswerException());
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_WRONG_ANSWER, $this->getResponseContent($response)->code);
    }

    public function test_answerAction_return_quiz_is_ended()
    {
        $beginQuiz = $this->createMock(BeginQuiz::class);
        $getQuiz = $this->createMock(GetQuiz::class);
        $askNewQuestion = $this->createMock(AskNewQuestion::class);
        $answerQuestion = $this->createMock(AnswerQuestion::class);
        $answerQuestion->expects($this->once())->method('execute')->willThrowException(new AppExceptions\QuizIsEndedException());
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('get')->with('answerId')->willReturn(10);

        $controller = new QuizController(
            $beginQuiz,
            $getQuiz,
            $askNewQuestion,
            $answerQuestion
        );

        $response = $controller->answerAction($request, 5);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(QuizController::CODE_QUIZ_IS_ENDED, $this->getResponseContent($response)->code);
    }

    private function getResponseContent(JsonResponse $response)
    {
        return json_decode($response->getContent());
    }
}