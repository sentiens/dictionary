<?php

namespace AppBundle\DTO;

use AppBundle\DTO\Quiz as QuizDTO;
use AppBundle\Entity\Quiz as QuizEntity;
use AppBundle\Entity\Word;

class QuizDTOConverter {

    public function convert(QuizEntity $quiz) {

        return new QuizDTO($quiz->getId(), $quiz->getStatus()->toNative(), $quiz->getScore(), $quiz->getName(),
            $quiz->getMistakesAvailable(), $quiz->hasUnansweredQuestion() ? $quiz->getQuestion()->getWord() : null,
            $quiz->hasUnansweredQuestion() ? array_map(function (Word $word) use ($quiz) {

                return new Answer($word->getId(), $word->getWord(), $quiz->getIsFailedAnswer($word));
            }, $quiz->getAnswers()->toArray()) : null);
    }
}