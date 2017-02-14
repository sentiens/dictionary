<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Value\Username;

class UsernameTest extends \PHPUnit_Framework_TestCase {

    public function test_it_throws_exception_if_not_string_provided() {

        $this->expectException(\InvalidArgumentException::class);
        new Username([]);
    }

    public function test_it_throws_exception_if_wrong_formatted_username_provided() {

        $this->expectException(\InvalidArgumentException::class);
        new Username('[]');
    }

    public function test_it_just_works() {

        $name = new Username('Василий');

        $this->assertEquals('Василий', $name->getValue());
        $this->assertEquals('Василий', $name->__toString());
    }
}