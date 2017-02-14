<?php

namespace Tests\Infrastructure\Entity;

use AppBundle\Entity\Quiz;
use AppBundle\Entity\QuizStatus;
use AppBundle\Entity\Word;
use AppBundle\Entity\Exception\WrongAnswerException;
use AppBundle\Value\Username;
use Tests\DBTestCase;

class QuizTest extends DBTestCase {
    
    public function test_it_saves_relations()
    {
        $wrongAnswers = [];
        $entities = [
            $question = Word::recordRussianWord('слон'),
            $rightAnswer = Word::recordEnglishWord('elephant'),
            $wrongAnswers[] = Word::recordEnglishWord('chair'),
            $wrongAnswers[] = Word::recordEnglishWord('moon'),
            $wrongAnswers[] = Word::recordEnglishWord('spear'),
            $quiz = Quiz::begin(new Username('Neo'))
        ];

        $quiz->askNewQuestion($question, $rightAnswer, $wrongAnswers);

        foreach ($entities as $e) {
            $this->em->persist($e);
        }
        $this->em->flush();
        $this->em->clear();

        $repository = $this->em->getRepository(Quiz::class);
        $wordRepository = $this->em->getRepository(Word::class);

        /**
         * @var Quiz $quiz
         */
        $quiz = $repository->find($quiz->getId());

        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->assertTrue($quiz->getStatus()->equalTo(QuizStatus::UnansweredQuestion()));
        $this->assertEquals(0, $quiz->getScore());
        $this->assertEquals('Neo', $quiz->getName());
        $this->assertInstanceOf(Word::class, $quiz->getQuestion());
        $this->assertEquals('слон', $quiz->getQuestion()->getWord());
        $this->assertCount(4, $quiz->getAnswers()); // assert it saves answers
        $this->assertCount(1, $quiz->getQuestions()); // assert it saves questions collection

        try {
            $quiz->answer($wordRepository->find($wrongAnswers[0]->getId()));
        } catch (WrongAnswerException $e) {}
        $this->em->persist($quiz);
        $this->em->flush();
        $this->em->clear();

        /**
         * @var Quiz $quiz
         */
        $quiz = $repository->find($quiz->getId());

        // assert it saves failed questions
        $this->assertTrue($quiz->getIsFailedAnswer($wordRepository->find($wrongAnswers[0]->getId())));

        $quiz->answer($wordRepository->find($rightAnswer->getId()));

        $question = Word::recordRussianWord('Свобода');
        $rightAnswer = Word::recordEnglishWord('freedom');
        $quiz->askNewQuestion(
            $question,
            $rightAnswer,
            [
                Word::recordEnglishWord('prison'),
                Word::recordEnglishWord('black'),
                Word::recordEnglishWord('counter')
            ]
        );

        $this->em->persist($quiz);
        $this->em->flush();
        $this->em->clear();

        $quiz = $repository->find($quiz->getId());

        $this->assertCount(2, $quiz->getQuestions());
    }
}