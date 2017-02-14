<?php

namespace Tests\Infrastructure\Repository;

use AppBundle\Entity\Quiz;
use AppBundle\Value\Username;
use Tests\DBTestCase;

class TestRepositoryTest extends DBTestCase {

    public function test_find()
    {
        $quiz = Quiz::begin(new Username('Vasily'));

        $this->em->persist($quiz);
        $this->em->flush();

        $this->em->detach($quiz);

        $repository = static::$kernel->getContainer()->get('app.service.quiz_repository');
        $quiz = $repository->find($quiz->getId());

        $this->assertNotNull($quiz);
        $this->assertEquals('Vasily', $quiz->getName());
    }
}