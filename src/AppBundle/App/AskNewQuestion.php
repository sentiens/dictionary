<?php

namespace AppBundle\App;

use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\App\Exception as AppExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Word;
use AppBundle\Repository\QuizRepository;
use AppBundle\Repository\TranslationRepository;
use AppBundle\Repository\WordRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

class AskNewQuestion {

    /**
     * @var QuizRepository
     */
    private $quizRepository;

    /**
     * @var WordRepository
     */
    private $wordRepository;

    /**
     * @var TranslationRepository
     */
    private $translationRepository;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        QuizRepository $quizRepository,
        WordRepository $wordRepository,
        TranslationRepository $translationRepository,
        EntityManager $em
    ) {

        $this->quizRepository = $quizRepository;
        $this->wordRepository = $wordRepository;
        $this->translationRepository = $translationRepository;
        $this->em = $em;
    }

    /**
     * @param int $id
     *
     * @throws AppExceptions\QuizIsEndedException
     * @throws AppExceptions\QuizNotFoundException
     * @throws AppExceptions\QuizAlreadyHasAQuestionException
     * @throws \Exception
     */
    public function execute(int $id) {

        /**
         * @var Quiz $quiz
         */
        $quiz = $this->quizRepository->find($id);

        if (!$quiz) {
            throw new AppExceptions\QuizNotFoundException;
        }

        if ($quiz->isEnded()) {
            throw new AppExceptions\QuizIsEndedException;
        }

        if ($quiz->hasUnansweredQuestion()) {
            throw new AppExceptions\QuizAlreadyHasAQuestionException;
        }

        $usedWordIds = $quiz->getUsedWords()->map(function (Word $word) {

            return $word->getId();
        });

        try {
            $question = $this->wordRepository->findRandomWord($usedWordIds->toArray());
        } catch (NoResultException $e) {
            if (count($usedWordIds) === 0) {
                throw new \Exception('No words.');
            } else { // there is no words for new question. quiz is ended
                $quiz->end();
                $this->em->persist($quiz);
                $this->em->flush();
                throw new AppExceptions\QuizIsEndedException;
            }
        }

        try {
            $translation = $this->translationRepository->findTranslationForWord($question->getId());
        } catch (NoResultException $e) {
            throw new \Exception(sprintf('Translation not found for Word #%d.', $question->getId()));
        }

        $rightAnswer = $translation->getTranslation($question);

        $wrongAnswers = $this->wordRepository->findRandomWord([$rightAnswer->getId()], $rightAnswer->getLang(), 3);

        if (count($wrongAnswers) !== 3) {
            throw new \Exception(sprintf('Not enough wrong words for answer #%d.', $rightAnswer->getId()));
        }

        $quiz->askNewQuestion($question, $rightAnswer, $wrongAnswers);

        $this->em->persist($quiz);
        $this->em->flush($quiz);
    }
}