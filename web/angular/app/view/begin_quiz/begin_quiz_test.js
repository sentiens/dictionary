'use strict';

describe('dictionary.beginQuiz module', function () {

  beforeEach(module('dictionary.beginQuiz'));

  describe('BeginQuizCtrl controller', function () {

    it('should begin quiz', inject(function ($controller, $rootScope, $q) {
      var $scope = $rootScope.$new();
      $scope.username = 'Neo';
      $scope.beginQuizForm = {
        $valid: true
      };

      var beginDefer = $q.defer();

      var quizService = {
        begin: function (name) {
          expect(name).toBe('Neo');
          return beginDefer.promise;
        }
      };
      spyOn(quizService, "begin").and.callThrough();

      var $location = {
        path: function (path) {
          expect(path).toBe('/quiz/10');
        }
      };
      spyOn($location, "path").and.callThrough();

      var BeginQuizCtrl = $controller('BeginQuizCtrl', {
        $scope: $scope,
        $location: $location,
        quizService: quizService
      });

      $scope.beginQuiz();
      beginDefer.resolve(10);
      $scope.$apply();

      expect(quizService.begin.calls.count()).toEqual(1);
      expect($location.path.calls.count()).toEqual(1);

    }));

  });
});