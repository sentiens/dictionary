<?php

namespace Tests\Acceptance;

use AppBundle\DataFixtures\ORM\LoadDictionaryData;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Word;
use AppBundle\Value\Username;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QuizAPITest extends WebTestCase {

    public function test_get() {
        $quiz = Quiz::begin(new Username('vasya'));
        $this->em->persist($quiz);
        $this->em->flush();

        $client = $this->createClient();
        $client->request('GET', '/quiz/'.$quiz->getId());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function test_begin()
    {
        $client = $this->createClient();
        $client->request('POST', '/quiz/', [
            'name' => 'Man'
        ]);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function test_answer()
    {
        $question = Word::recordEnglishWord('car');
        $rightAnswer = Word::recordRussianWord('машина');
        $wrongAnswers = [
            Word::recordRussianWord('телевизор'),
            Word::recordRussianWord('астрология'),
            Word::recordRussianWord('мрамор')
        ];

        $quiz = Quiz::begin(new Username('vasya'));
        $quiz->askNewQuestion($question, $rightAnswer, $wrongAnswers);
        $this->em->persist($quiz);
        $this->em->flush();

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/quiz/' . $quiz->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ),
            json_encode([
                'answerId' => $rightAnswer->getId()
            ])
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function test_ask()
    {
        $fixtureLoader = new ContainerAwareLoader(self::$kernel->getContainer());
        $fixtureLoader->addFixture(new LoadDictionaryData());

        $fixtureExecutor = new ORMExecutor($this->em, new ORMPurger($this->em));
        $fixtureExecutor->execute($fixtureLoader->getFixtures());

        $quiz = Quiz::begin(new Username('vasya'));
        $this->em->persist($quiz);
        $this->em->flush();

        $client = $this->createClient();
        $client->request('POST', '/quiz/' . $quiz->getId());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
