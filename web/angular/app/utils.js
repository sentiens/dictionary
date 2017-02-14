'use strict';

angular.module('dictionary.utils', []).constant('fixAssetUrl', function (url) {
  return 'angular/app/' + url;
});