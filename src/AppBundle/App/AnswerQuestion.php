<?php

namespace AppBundle\App;

use AppBundle\App\Exception as AppExceptions;
use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Word;
use AppBundle\Repository\QuizRepository;
use AppBundle\Repository\WordRepository;
use Doctrine\ORM\EntityManager;

class AnswerQuestion {

    /**
     * @var QuizRepository
     */
    private $quizRepository;

    /**
     * @var WordRepository
     */
    private $wordRepository;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(QuizRepository $quizRepository, WordRepository $wordRepository, EntityManager $em) {

        $this->quizRepository = $quizRepository;
        $this->wordRepository = $wordRepository;
        $this->em = $em;
    }


    /**
     * @param $quizId
     * @param $wordId
     *
     * @throws AppExceptions\QuizHasNoQuestionToAnswerException
     * @throws AppExceptions\RepeatingAnswerException
     * @throws AppExceptions\QuizIsEndedException
     * @throws AppExceptions\QuizNotFoundException
     * @throws AppExceptions\WordNotFoundException
     * @throws AppExceptions\WrongAnswerException
     */
    public function execute($quizId, $wordId) {

        /**
         * @var Quiz $quiz
         */
        $quiz = $this->quizRepository->find($quizId);

        if (!$quiz) {
            throw new AppExceptions\QuizNotFoundException;
        }

        if ($quiz->isEnded()) {
            throw new AppExceptions\QuizIsEndedException;
        }

        /**
         * @var Word $word
         */
        $word = $this->wordRepository->find($wordId);

        if (!$word) {
            throw new AppExceptions\WordNotFoundException;
        }

        try {
            $quiz->answer($word);
            $this->em->persist($quiz);
            $this->em->flush();
        } catch (DomainExceptions\WrongAnswerException $e) {
            $this->em->persist($quiz);
            $this->em->flush();
            throw new AppExceptions\WrongAnswerException;
        } catch (DomainExceptions\QuizIsEndedException $e) {
            throw new AppExceptions\QuizIsEndedException;
        } catch (DomainExceptions\QuizHasNoQuestionToAnswerException $e) {
            throw new AppExceptions\QuizHasNoQuestionToAnswerException;
        } catch (DomainExceptions\RepeatingAnswerException $e) {
            throw new AppExceptions\RepeatingAnswerException;
        } catch (DomainExceptions\AnswerDoesNotBelongToTheQuestionException $e) {
            throw new AppExceptions\WordNotFoundException;
        }
    }
}