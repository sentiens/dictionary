'use strict';

angular.module('dictionary.quizService', []).factory('quizService', function ($http, $q) {
  return {
    begin: function (name) {
      return $http.post('/quiz/', {name: name}).then(function (res) {
        return res.data.data.id;
      }, function (res) {
        return $q.reject(res.data.code);
      });
    },
    'get': function (id) {
      return $http.get('/quiz/' + id)
        .then(function (res) {
          return res.data.data;
        }, function (res) {
          return $q.reject(res.data.code);
        });
    },
    ask: function (id) {
      return $http.post('/quiz/' + id)
        .then(function (res) {
          return res.data.data;
        }, function (res) {
          return $q.reject(res.data.code);
        });
    },
    answer: function (quizId, answerId) {
      return $http.put('/quiz/' + quizId, {answerId: answerId})
        .then(function (res) {
          return res.data.data;
        }, function (res) {
          return $q.reject(res.data.code);
        });
    }
  };
}).constant('QUIZ_SERVICE_STATUS_CODES', {
  CODE_JUST_ERROR: 0,
  CODE_WRONG_ANSWER: 1,
  CODE_NO_QUESTION: 2,
  CODE_QUIZ_IS_ENDED: 3,
  CODE_HAS_UNANSWERED_QUESTION: 4
});