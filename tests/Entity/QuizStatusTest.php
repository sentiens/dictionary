<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\QuizStatus;

class QuizStatusTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_invalid() {

        $this->expectException(\InvalidArgumentException::class);
        new QuizStatus(10);
    }
}