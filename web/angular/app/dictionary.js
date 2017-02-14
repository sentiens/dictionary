'use strict';

angular.module('dictionary', [
  'ngRoute',
  'ngMessages',
  'ngAnimate',
  'angular-loading-bar',
  'dictionary.beginQuiz',
  'dictionary.quiz'
]).config(function ($locationProvider, $routeProvider, cfpLoadingBarProvider) {
  $locationProvider.hashPrefix('!');

  $routeProvider.otherwise({redirectTo: '/'});

  cfpLoadingBarProvider.parentSelector = '#loading-bar-container';
});
