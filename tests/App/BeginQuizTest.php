<?php

namespace Tests\AppBundle\Entity;

use AppBundle\App\BeginQuiz;
use AppBundle\App\Exception\InvalidUsernameException;
use AppBundle\Entity\Quiz;
use Doctrine\ORM\EntityManager;

class BeginQuizTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_invalid_username() {

        $em = $this->createMock(EntityManager::class);

        $beginQuiz = new BeginQuiz($em);

        $this->expectException(InvalidUsernameException::class);
        $beginQuiz->execute('!(%*&');
    }

    public function test_it_begins_quiz() {

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('persist')->with($this->callback(function (Quiz $test) {

            $test->_setId(1);
            return $test->getName() === 'Richard';
        }));
        $em->expects($this->once())->method('flush');

        $beginQuiz = new BeginQuiz($em);

        $result = $beginQuiz->execute('Richard');

        $this->assertEquals(1, $result);
    }
}