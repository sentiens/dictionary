<?php

namespace Tests\AppBundle\App;

use AppBundle\App\GetQuiz;
use AppBundle\App\Exception\QuizNotFoundException;
use AppBundle\DTO\Quiz as QuizDTO;
use AppBundle\DTO\QuizDTOConverter;
use AppBundle\Entity\Quiz;
use AppBundle\Repository\QuizRepository;

class GetQuizTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_not_found() {

        $repository = $this->createMock(QuizRepository::class);
        $repository->expects($this->once())->method('find')->with(1)->willReturn(null);

        $getQuiz = new GetQuiz($repository, $this->createMock(QuizDTOConverter::class));

        $this->expectException(QuizNotFoundException::class);
        $getQuiz->execute(1);
    }

    public function test_it_returns_dto() {

        $quiz = $this->createMock(Quiz::class);

        $repository = $this->createMock(QuizRepository::class);
        $repository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $dto = $this->createMock(QuizDTO::class);

        $converter = $this->createMock(QuizDTOConverter::class);
        $converter->expects($this->once())->method('convert')->with($quiz)->willReturn($dto);

        $getQuiz = new GetQuiz($repository, $converter);

        $result = $getQuiz->execute(1);

        $this->assertEquals($dto, $result);
    }
}