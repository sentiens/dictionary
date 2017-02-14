<?php

namespace AppBundle\DTO;

class Quiz {

    public $id;

    public $status;

    public $score;

    public $name;

    public $mistakesAvailable;

    public $question;

    public $answers;

    public function __construct(
        int $id,
        int $status,
        int $score,
        string $name,
        int $mistakesAvailable,
        string $question = null,
        array $answers = null
    ) {

        if ($answers !== null) {
            foreach ($answers as $answer) {
                if (!$answer instanceof Answer) {
                    throw new \InvalidArgumentException('Answers should be instances of Answer class.');
                }
            }
        }

        $this->id = $id;
        $this->status = $status;
        $this->score = $score;
        $this->name = $name;
        $this->mistakesAvailable = $mistakesAvailable;
        $this->question = $question;
        $this->answers = $answers;
    }
}