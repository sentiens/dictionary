<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Embeddable */
class QuizStatus {

    private static $STATUS_NO_QUESTION = 2;

    private static $STATUS_HAS_UNANSWERED_QUESTION = 1;

    private static $STATUS_ENDED = -1;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    public function __construct(int $status) {

        if (!in_array($status, [
            static::$STATUS_NO_QUESTION,
            static::$STATUS_HAS_UNANSWERED_QUESTION,
            static::$STATUS_ENDED,
        ])
        ) {
            throw new \InvalidArgumentException('Invalid status primitive provided.');
        }

        $this->status = $status;
    }

    public static function NoQuestion() {

        return new static(static::$STATUS_NO_QUESTION);
    }

    public static function UnansweredQuestion() {

        return new static(static::$STATUS_HAS_UNANSWERED_QUESTION);
    }

    public static function Ended() {

        return new static(static::$STATUS_ENDED);
    }

    public function equalTo(QuizStatus $status) {

        return $this->status === $status->status;
    }

    public function toNative() : int {

        return $this->status;
    }
}