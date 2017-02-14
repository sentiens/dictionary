'use strict';

angular.module('dictionary.domain', []).constant('QUIZ_STATUS', {
  STATUS_NO_QUESTION: 2,
  STATUS_HAS_UNANSWERED_QUESTION: 1,
  STATUS_ENDED: -1
});