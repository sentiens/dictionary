<?php

namespace Tests\AppBundle\App;

use AppBundle\App\AskNewQuestion;
use AppBundle\App\Exception as AppExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Translation;
use AppBundle\Entity\Word;
use AppBundle\Repository\QuizRepository;
use AppBundle\Repository\TranslationRepository;
use AppBundle\Repository\WordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

class AskNewQuestionTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_quiz_not_found_exception() {

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn(null);
        $wordRepository = $this->createMock(WordRepository::class);
        $translationRepository = $this->createMock(TranslationRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $this->expectException(AppExceptions\QuizNotFoundException::class);
        $command->execute(1);
    }

    public function test_it_throws_quiz_is_ended() {

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('isEnded')->willReturn(true);

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);
        $wordRepository = $this->createMock(WordRepository::class);
        $translationRepository = $this->createMock(TranslationRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $this->expectException(AppExceptions\QuizIsEndedException::class);
        $command->execute(1);
    }

    public function test_it_throws_quiz_already_has_a_question() {

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('isEnded')->willReturn(false);
        $quiz->expects($this->once())->method('hasUnansweredQuestion')->willReturn(true);

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);
        $wordRepository = $this->createMock(WordRepository::class);
        $translationRepository = $this->createMock(TranslationRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $this->expectException(AppExceptions\QuizAlreadyHasAQuestionException::class);
        $command->execute(1);
    }

    public function test_it_throws_exception_if_no_words_in_database() {

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('isEnded')->willReturn(false);
        $quiz->expects($this->once())->method('hasUnansweredQuestion')->willReturn(false);
        $quiz->expects($this->once())->method('getUsedWords')->willReturn(new ArrayCollection());

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);
        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('findRandomWord')->willThrowException(new NoResultException());

        $translationRepository = $this->createMock(TranslationRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $this->expectException(\Exception::class);
        $command->execute(1);
    }

    public function test_it_throws_exception_if_no_more_unused_words() {

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('isEnded')->willReturn(false);
        $quiz->expects($this->once())->method('hasUnansweredQuestion')->willReturn(false);
        $usedWord = $this->createMock(Word::class);
        $usedWord->expects($this->once())->method('getId')->willReturn(10);

        $quiz->expects($this->once())->method('getUsedWords')->willReturn(new ArrayCollection([
            $usedWord
        ]));

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);
        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->once())->method('findRandomWord')->willThrowException(new NoResultException());

        $translationRepository = $this->createMock(TranslationRepository::class);
        $em = $this->createMock(EntityManager::class);

        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $this->expectException(AppExceptions\QuizIsEndedException::class);
        $command->execute(1);
    }

    public function test_it_works() {

        $word1 = $this->createMock(Word::class);
        $word1->expects($this->once())->method('getId')->willReturn(1);

        $word2 = $this->createMock(Word::class);
        $word2->expects($this->once())->method('getId')->willReturn(2);

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('isEnded')->willReturn(false);
        $quiz->expects($this->once())->method('hasUnansweredQuestion')->willReturn(false);
        $quiz->expects($this->once())->method('getUsedWords')->willReturn(new ArrayCollection([
            $word1,
            $word2
        ]));

        $question = $this->createMock(Word::class);
        $question->expects($this->once())->method('getId')->willReturn(4);

        $quizRepository = $this->createMock(QuizRepository::class);
        $quizRepository->expects($this->once())->method('find')->with(1)->willReturn($quiz);

        $wrongAnswers = [
            $this->createMock(Word::class),
            $this->createMock(Word::class),
            $this->createMock(Word::class)
        ];
        $wordRepository = $this->createMock(WordRepository::class);
        $wordRepository->expects($this->exactly(2))->method('findRandomWord')->withConsecutive([[1, 2]],
            [[5], Word::LANG_RU, 3])->willReturnOnConsecutiveCalls($question, $wrongAnswers);

        $rightAnswer = $this->createMock(Word::class);
        $rightAnswer->expects($this->once())->method('getId')->willReturn(5);
        $rightAnswer->expects($this->once())->method('getLang')->willReturn(Word::LANG_RU);

        $translation = $this->createMock(Translation::class);
        $translation->expects($this->once())->method('getTranslation')->with($question)->willReturn($rightAnswer);

        $translationRepository = $this->createMock(TranslationRepository::class);
        $translationRepository->expects($this->once())->method('findTranslationForWord')->with(4)->willReturn($translation);

        $quiz->expects($this->once())->method('askNewQuestion')->with($question, $rightAnswer, $wrongAnswers);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('persist')->with($quiz);
        $em->expects($this->once())->method('flush');
        $command = new AskNewQuestion($quizRepository, $wordRepository, $translationRepository, $em);

        $command->execute(1);
    }
}