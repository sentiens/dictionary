'use strict';

angular.module('dictionary.beginQuiz', ['dictionary.utils', 'ngRoute', 'dictionary.quizService'])

  .config(function ($routeProvider, fixAssetUrl) {
    $routeProvider.when('/', {
      templateUrl: fixAssetUrl('view/begin_quiz/begin_quiz.html'),
      controller: 'BeginQuizCtrl'
    });
  })
  .controller('BeginQuizCtrl', function ($scope, $location, quizService) {
    $scope.beginQuiz = function () {
      if ( ! $scope.beginQuizForm.$valid) {
        return;
      }
      quizService.begin($scope.username).then(function (id) {
        $location.path('/quiz/' + id);
      });
    };
  });