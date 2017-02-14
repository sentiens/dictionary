'use strict';

describe('dictionary.quizService module', function () {
  beforeEach(module('dictionary.quizService'));
  var $httpBackend, quizService;

  beforeEach(inject(function (_$httpBackend_, _quizService_) {
    $httpBackend = _$httpBackend_;
    quizService = _quizService_;
  }));

  describe('quizService factory', function () {
    describe('quizService get method', function () {
      it('should do GET request and return data from response', function (done) {
        $httpBackend.expectGET('/quiz/5').respond({
          data: {
            id: 5,
            name: 'Neo'
          }
        });

        quizService.get(5).then(function (resp) {
          expect(resp.id).toBe(5);
          expect(resp.name).toBe('Neo');
          done()
        });
        $httpBackend.flush();
      });

      it('should return error code from response', function (done) {
        $httpBackend.expectGET('/quiz/5').respond(404, {
          code: 0
        });

        quizService.get(5).then(function () {
        }, function (code) {
          expect(code).toBe(0);
          done();
        });
        $httpBackend.flush();
      });
    });

    describe('quizService begin method', function () {
      it('should do POST request and return id from response', function (done) {
        $httpBackend.expectPOST('/quiz/', {
          name: 'Neo'
        }).respond({
          data: {
            id: 5
          }
        });

        quizService.begin('Neo').then(function (id) {
          expect(id).toBe(5);
          done()
        });
        $httpBackend.flush();
      });

      it('should return error code', function (done) {
        $httpBackend.expectPOST('/quiz/', {
          name: 'Neo'
        }).respond(404, {
          code: 0
        });

        quizService.begin('Neo').then(function () {
        }, function (code) {
          expect(code).toBe(0);
          done()
        });
        $httpBackend.flush();
      });
    });

    describe('quizService ask method', function () {
      it('should do POST request', function (done) {
        $httpBackend.expectPOST('/quiz/5').respond({});

        quizService.ask(5).then(function () {
          done()
        });
        $httpBackend.flush();
      });

      it('should return error code', function (done) {
        $httpBackend.expectPOST('/quiz/5').respond(404, {
          code: 0
        });

        quizService.ask(5).then(function () {
        }, function (code) {
          expect(code).toBe(0);
          done();
        });
        $httpBackend.flush();
      });
    });

    describe('quizService answer method', function () {
      it('should do PUT request with given quizId and answerId', function (done) {
        $httpBackend.expectPUT('/quiz/5', {
          answerId: 6
        }).respond({});

        quizService.answer(5, 6).then(function () {
          done()
        });
        $httpBackend.flush();
      });

      it('should return error code', function (done) {
        $httpBackend.expectPUT('/quiz/5').respond(404, {
          code: 0
        });

        quizService.answer(5, 6).then(function () {
        }, function (code) {
          expect(code).toBe(0);
          done();
        });
        $httpBackend.flush();
      });
    });
  });
});