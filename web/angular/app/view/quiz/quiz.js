'use strict';

angular.module('dictionary.quiz', ['dictionary.utils', 'dictionary.domain', 'ngRoute', 'dictionary.quizService'])
  .config(function ($routeProvider, fixAssetUrl) {
    $routeProvider.when('/quiz/:id', {
      templateUrl: fixAssetUrl('view/quiz/quiz.html'),
      controller: 'AnswerQuizCtrl',
      resolve: {
        quiz: function (quizService, $route, $location) {
          return quizService.get($route.current.params.id).catch(function () {
            $location.path('/');
          });
        }
      }
    });
  })
  .controller('AnswerQuizCtrl', function ($scope, quizService, QUIZ_SERVICE_STATUS_CODES, QUIZ_STATUS, quiz, $timeout) {
    $scope.quiz = quiz;
    $scope.showAnswerWasWrong = false;

    function handleAskError(code) {
      switch (code) {
        case QUIZ_SERVICE_STATUS_CODES.CODE_QUIZ_IS_ENDED:
          refreshQuiz();
          break;
        default: throw 'Quiz service error code ' + code;
      }
    }
    if ($scope.quiz.status == QUIZ_STATUS.STATUS_NO_QUESTION) {
      quizService.ask(quiz.id).then(refreshQuiz, handleAskError);
    }
    
    function refreshQuiz() {
      quizService.get(quiz.id).then(function (quiz) {
        $scope.quiz = quiz;
      });
    }

    $scope.quizHasQuestion = function () {
      return $scope.quiz.status === QUIZ_STATUS.STATUS_HAS_UNANSWERED_QUESTION;
    };

    $scope.quizEnded = function () {
      return $scope.quiz.status === QUIZ_STATUS.STATUS_ENDED;
    };

    $scope.answerQuiz = function (answer) {
      if (answer.failed) return;

      quizService.answer($scope.quiz.id, answer.id).then(function () {
        quizService.ask($scope.quiz.id).then(refreshQuiz, handleAskError);
      }, function (code) {
        $scope.showAnswerWasWrong = true;
        $timeout(function () {
          $scope.showAnswerWasWrong = false;
        }, 2000);
        switch (code) {
          case QUIZ_SERVICE_STATUS_CODES.CODE_WRONG_ANSWER:
          case QUIZ_SERVICE_STATUS_CODES.CODE_QUIZ_IS_ENDED:
            refreshQuiz();
            break;
          default: throw 'Quiz service error code ' + code;
        }
      });
    };

    $scope.numberOf = function (n) {
      return new Array(n);
    };
  });