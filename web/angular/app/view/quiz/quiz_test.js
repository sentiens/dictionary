'use strict';

describe('dictionary.quiz module', function () {
  beforeEach(module('dictionary.quiz'));

  var quizWithQuestion, QUIZ_STATUS;

  beforeEach(inject(function (_QUIZ_STATUS_) {
    QUIZ_STATUS = _QUIZ_STATUS_;
    quizWithQuestion = {
      id: 1,
      status: QUIZ_STATUS.STATUS_HAS_UNANSWERED_QUESTION,
      score: 2,
      name: 'Neo',
      mistakesAvailable: 3,
      question: 'apple',
      answers: [
        {
          id: 1,
          word: 'яблоко',
          failed: false
        },
        {
          id: 5,
          word: 'зонт',
          failed: false
        },
        {
          id: 8,
          word: 'герой',
          failed: false
        },
        {
          id: 25,
          word: 'поклон',
          failed: false
        }
      ]
    };
  }));

  describe('AnswerQuizCtrl controller', function () {

    describe('getters', function () {
      var $scope, AnswerQuizCtrl, QUIZ_STATUS;

      beforeEach(inject(function (_$controller_, _$rootScope_, _QUIZ_STATUS_) {
        QUIZ_STATUS = _QUIZ_STATUS_;
        $scope = _$rootScope_.$new();
        AnswerQuizCtrl = _$controller_('AnswerQuizCtrl', {
          $scope: $scope,
          quiz: {
            id: 1,
            status: QUIZ_STATUS.STATUS_NO_QUESTION,
            score: 0,
            name: 'Neo',
            mistakesAvailable: 3
          }
        });
      }));

      describe('quizHasQuestion method', function () {
        it('should return false if status no question', function () {
          $scope.quiz.status = QUIZ_STATUS.STATUS_NO_QUESTION;

          expect($scope.quizHasQuestion()).toBe(false);
        });

        it('returns true if status STATUS_HAS_UNANSWERED_QUESTION', function () {
          $scope.quiz.status = QUIZ_STATUS.STATUS_HAS_UNANSWERED_QUESTION;

          expect($scope.quizHasQuestion()).toBe(true);
        });
      });

      describe('quizEnded method', function () {
        it('should return false if status NO_QUESTION', function () {
          expect($scope.quizEnded()).toBe(false);
        });

        it('returns true if status STATUS_ENDED', function () {
          $scope.quiz.status = QUIZ_STATUS.STATUS_ENDED;

          expect($scope.quizEnded()).toBe(true);
        });
      });

      describe('numberOf method', function () {
        it('should return array of given size', function () {
          expect($scope.numberOf(5).length).toBe(5);
        });
      });
    });

    describe('answerQuiz method', function () {
      it('should do nothing if failed answer provided', inject(function ($controller, $q, $rootScope) {
        var $scope = $rootScope.$new();

        var quizService = {
          answer: function (id) {
          }
        };

        spyOn(quizService, "answer").and.callThrough();

        var AnswerQuizCtrl = $controller('AnswerQuizCtrl', {
          $scope: $scope,
          quiz: angular.copy(quizWithQuestion),
          quizService: quizService
        });

        $scope.answerQuiz({
          id: 5,
          word: 'apple',
          failed: true
        });

        expect(quizService.answer.calls.count()).toBe(0);
      }));

      describe('behaviour', function () {
        var $scope, getDefer, answerDefer, askDefer, quizService, AnswerQuizCtrl, QUIZ_SERVICE_STATUS_CODES, $controller;

        beforeEach(inject(function (_$controller_, $q, $rootScope, _QUIZ_SERVICE_STATUS_CODES_) {
          QUIZ_SERVICE_STATUS_CODES = _QUIZ_SERVICE_STATUS_CODES_;
          $controller = _$controller_;
          $scope = $rootScope.$new();
          getDefer = $q.defer();
          answerDefer = $q.defer();
          askDefer = $q.defer();

          quizService = {
            'get': function (id) {
              expect(id).toBe(1);
              return getDefer.promise;
            },
            answer: function (id, answerId) {
              expect(id).toBe(1);
              expect(answerId).toBe(1);
              return answerDefer.promise;
            },
            ask: function (id) {
              expect(id).toBe(1);
              return askDefer.promise;
            }
          };

          spyOn(quizService, "get").and.callThrough();
          spyOn(quizService, "ask").and.callThrough();
          spyOn(quizService, "answer").and.callThrough();
        }));

        describe('on initialization', function () {
          it('should ask question if quiz with no question provided', function () {
            AnswerQuizCtrl = $controller('AnswerQuizCtrl', {
              $scope: $scope,
              quiz: {
                id: 1,
                status: QUIZ_STATUS.STATUS_NO_QUESTION
              },
              quizService: quizService
            });

            expect(quizService.ask.calls.count()).toEqual(1);

            expect(quizService.get.calls.count()).toEqual(0);

            askDefer.resolve();
            $scope.$apply();
            expect(quizService.get.calls.count()).toEqual(1);
            var refreshedQuiz = angular.copy(quizWithQuestion);
            getDefer.resolve(refreshedQuiz);
            $scope.$apply();
            expect($scope.quiz).toBe(refreshedQuiz);
          });
        });

        describe('answer method', function () {
          beforeEach(function () {
            AnswerQuizCtrl = $controller('AnswerQuizCtrl', {
              $scope: $scope,
              quiz: angular.copy(quizWithQuestion),
              quizService: quizService
            });
          });

          it('should send right answer and refresh word', function () {
            $scope.answerQuiz({
              id: 1,
              failed: false
            });

            expect(quizService.answer.calls.count()).toBe(1);

            answerDefer.resolve();
            $scope.$apply();

            expect(quizService.ask.calls.count()).toBe(1);

            askDefer.resolve();
            $scope.$apply();

            expect(quizService.get.calls.count()).toBe(1);

            var refreshedQuiz = angular.copy(quizWithQuestion);
            getDefer.resolve(refreshedQuiz);

            $scope.$apply();

            expect($scope.quiz).toBe(refreshedQuiz);
          });

          it('should send wrong answer and refresh word', function () {
            $scope.answerQuiz({
              id: 1,
              failed: false
            });

            expect(quizService.answer.calls.count()).toBe(1);

            answerDefer.reject(QUIZ_SERVICE_STATUS_CODES.CODE_WRONG_ANSWER);
            $scope.$apply();
            expect(quizService.ask.calls.count()).toBe(0);
            expect(quizService.get.calls.count()).toBe(1);

            var refreshedQuiz = angular.copy(quizWithQuestion);
            getDefer.resolve(refreshedQuiz);

            $scope.$apply();

            expect($scope.quiz).toBe(refreshedQuiz);
          });

          it('should catch quiz end', function () {
            $scope.answerQuiz({
              id: 1,
              failed: false
            });

            expect(quizService.answer.calls.count()).toBe(1);

            answerDefer.resolve();
            $scope.$apply();
            expect(quizService.ask.calls.count()).toBe(1);
            expect(quizService.get.calls.count()).toBe(0);

            askDefer.reject(QUIZ_SERVICE_STATUS_CODES.CODE_QUIZ_IS_ENDED);

            var refreshedQuiz = angular.copy(quizWithQuestion);
            getDefer.resolve(refreshedQuiz);

            $scope.$apply();

            expect($scope.quiz).toBe(refreshedQuiz);
          });
        });
      });
    });
  });
});