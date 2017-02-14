<?php

namespace AppBundle\App;

use AppBundle\App\Exception as AppExceptions;
use AppBundle\DTO\Quiz as QuizDTO;
use AppBundle\DTO\QuizDTOConverter;
use AppBundle\Entity as DomainExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Repository\QuizRepository;

class GetQuiz {

    /**
     * @var QuizRepository
     */
    private $repository;

    /**
     * @var QuizDTOConverter
     */
    private $converter;

    public function __construct(QuizRepository $repository, QuizDTOConverter $converter) {

        $this->repository = $repository;
        $this->converter = $converter;
    }

    /**
     * @param int $id
     *
     * @return QuizDTO|null
     * @throws AppExceptions\QuizNotFoundException
     */
    public function execute(int $id) {

        /**
         * @var Quiz $quiz
         */
        $quiz = $this->repository->find($id);

        if (!$quiz) {
            throw new AppExceptions\QuizNotFoundException;
        }

        return $this->converter->convert($quiz);
    }
}