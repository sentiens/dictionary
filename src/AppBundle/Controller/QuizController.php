<?php

namespace AppBundle\Controller;

use AppBundle\App\AnswerQuestion;
use AppBundle\App\AskNewQuestion;
use AppBundle\App\BeginQuiz;
use AppBundle\App\GetQuiz;
use AppBundle\App\Exception as AppExceptions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QuizController {

    const CODE_JUST_ERROR = 0;
    const CODE_WRONG_ANSWER = 1;
    const CODE_NO_QUESTION = 2;
    const CODE_QUIZ_IS_ENDED = 3;
    const CODE_HAS_UNANSWERED_QUESTION = 4;

    /**
     * @var BeginQuiz
     */
    private $beginQuiz;

    /**
     * @var GetQuiz
     */
    private $getQuiz;

    /**
     * @var AskNewQuestion
     */
    private $askNewQuestion;

    /**
     * @var AnswerQuestion
     */
    private $answerQuestion;

    public function __construct(
        BeginQuiz $beginQuiz,
        GetQuiz $getQuiz,
        AskNewQuestion $askNewQuestion,
        AnswerQuestion $answerQuestion
    ) {

        $this->beginQuiz = $beginQuiz;
        $this->getQuiz = $getQuiz;
        $this->askNewQuestion = $askNewQuestion;
        $this->answerQuestion = $answerQuestion;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function beginAction(Request $request) {
        $name = $request->get('name');

        if ( ! $name || ! is_string($name)) {
            return $this->sendErrorResponse('Name should be a string.');
        }

        try {
            return $this->sendDataResponse([
                'id' => $this->beginQuiz->execute($name)
            ]);
        } catch (AppExceptions\InvalidUsernameException $e) {
            return $this->sendErrorResponse('Invalid name format.');
        }
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function getAction(int $id) {
        try {
            return $this->sendDataResponse(
                $this->getQuiz->execute($id)
            );
        } catch (AppExceptions\QuizNotFoundException $e) {
            return $this->sendQuizNotFoundResponse();
        }
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function askAction(int $id) {
        try {
            $this->askNewQuestion->execute($id);
            return $this->sendOk();
        } catch (AppExceptions\QuizNotFoundException $e) {
            return $this->sendQuizNotFoundResponse();
        } catch (AppExceptions\QuizIsEndedException $e) {
            return $this->sendQuizIsEndedResponse();
        } catch (AppExceptions\QuizAlreadyHasAQuestionException $e) {
            return $this->sendErrorResponse('There is unanswered question.', self::CODE_HAS_UNANSWERED_QUESTION);
        }
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function answerAction(Request $request, $id) {
        $wordId = $request->get('answerId');

        if (!$wordId || !is_int($wordId)) {
            return $this->sendErrorResponse('Invalid word id.');
        }

        try {
            $this->answerQuestion->execute($id, $wordId);
            return $this->sendOk();
        } catch (AppExceptions\QuizNotFoundException $e) {
            return $this->sendQuizNotFoundResponse();
        } catch (AppExceptions\WordNotFoundException $e) {
            return $this->sendErrorResponse('Answer not found.', self::CODE_JUST_ERROR, JsonResponse::HTTP_NOT_FOUND);
        } catch (AppExceptions\WrongAnswerException $e) {
            return $this->sendErrorResponse('Answer is wrong.', self::CODE_WRONG_ANSWER);
        } catch (AppExceptions\QuizHasNoQuestionToAnswerException $e) {
            return $this->sendErrorResponse('No question to answer.', self::CODE_NO_QUESTION);
        } catch (AppExceptions\QuizIsEndedException $e) {
            return $this->sendQuizIsEndedResponse();
        }
    }

    /**
     * @return JsonResponse
     */
    private function sendQuizNotFoundResponse()
    {
        return $this->sendErrorResponse(
            'Quiz not found.',
            self::CODE_JUST_ERROR,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @return JsonResponse
     */
    private function sendQuizIsEndedResponse()
    {
        return $this->sendErrorResponse(
            'Quiz is ended.',
            self::CODE_QUIZ_IS_ENDED
        );
    }

    /**
     * @param $message
     * @param int $code
     * @param int $httpCode
     * @param array $data
     *
     * @return JsonResponse
     */
    private function sendErrorResponse(
        $message,
        $code = self::CODE_JUST_ERROR,
        $httpCode = Response::HTTP_UNPROCESSABLE_ENTITY,
        $data = []
    ) {

        return $this->sendResponse([
            'message' => $message,
            'code' => $code,
            'data' => $data
        ], $httpCode);
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    private function sendDataResponse($data) {
        return $this->sendResponse([
            'data' => $data
        ]);
    }

    /**
     * @return JsonResponse
     */
    private function sendOk() {
        return $this->sendResponse();
    }

    /**
     * @param array $data
     * @param int $httpCode
     *
     * @return JsonResponse
     */
    private function sendResponse($data = [], $httpCode = Response::HTTP_OK) {

        return JsonResponse::create($data, $httpCode);
    }
}
