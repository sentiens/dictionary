<?php

namespace AppBundle\App;

use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\App\Exception as AppExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Value\Username;
use Doctrine\ORM\EntityManager;

class BeginQuiz {

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em) {

        $this->em = $em;
    }

    /**
     * @param string $name
     *
     * @return int
     * @throws AppExceptions\InvalidUsernameException
     */
    public function execute(string $name) : int {

        try {
            $username = new Username($name);
        } catch (\InvalidArgumentException $e) {
            throw new AppExceptions\InvalidUsernameException;
        }

        $quiz = Quiz::begin($username);

        $this->em->persist($quiz);
        $this->em->flush();

        return $quiz->getId();
    }
}