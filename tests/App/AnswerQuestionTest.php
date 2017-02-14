<?php

namespace Tests\AppBundle\App;

use AppBundle\App\AnswerQuestion;
use AppBundle\App\Exception as AppExceptions;
use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Word;
use AppBundle\Repository\QuizRepository;
use AppBundle\Repository\WordRepository;
use Doctrine\ORM\EntityManager;

class AnswerQuestionTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_quiz_not_found() {

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn(null);
        $wordRepository = $this->createMock(WordRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $this->expectException(AppExceptions\QuizNotFoundException::class);
        $command->execute(1, 2);
    }

    public function test_it_throws_word_not_found() {

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($this->createMock(Quiz::class));
        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('find')->with(2)->willReturn(null);
        $em = $this->createMock(EntityManager::class);

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $this->expectException(AppExceptions\WordNotFoundException::class);
        $command->execute(1, 2);
    }

    public function test_right_answer() {

        $word = $this->createMock(Word::class);

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('answer')->with($word);

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('find')->with(2)->willReturn($word);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('persist')->with($quiz);
        $em->expects($this->once())->method('flush');

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $command->execute(1, 2);
    }

    public function test_wrong_answer() {

        $word = $this->createMock(Word::class);

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('answer')->with($word)->willThrowException(new DomainExceptions\WrongAnswerException());
        $quiz->expects($this->once())->method('isEnded')->willReturn(false);

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('find')->with(2)->willReturn($word);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('persist')->with($quiz);
        $em->expects($this->once())->method('flush');

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $this->expectException(AppExceptions\WrongAnswerException::class);
        $command->execute(1, 2);
    }

    public function test_no_question() {

        $word = $this->createMock(Word::class);

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('answer')->with($word)->willThrowException(new DomainExceptions\QuizHasNoQuestionToAnswerException());

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('find')->with(2)->willReturn($word);

        $em = $this->createMock(EntityManager::class);

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $this->expectException(AppExceptions\QuizHasNoQuestionToAnswerException::class);
        $command->execute(1, 2);
    }

    public function test_answer_does_not_belong_converts_to_word_not_found() {

        $word = $this->createMock(Word::class);

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('answer')->with($word)->willThrowException(new DomainExceptions\AnswerDoesNotBelongToTheQuestionException());

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('find')->with(2)->willReturn($word);

        $em = $this->createMock(EntityManager::class);

        $command = new AnswerQuestion($quizRepository, $wordRepository, $em);

        $this->expectException(AppExceptions\WordNotFoundException::class);
        $command->execute(1, 2);
    }
}