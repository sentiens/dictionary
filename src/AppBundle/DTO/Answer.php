<?php

namespace AppBundle\DTO;

class Answer {

    public $id;

    public $word;

    public $failed;

    public function __construct(int $id, string $word, bool $failed) {

        $this->id = $id;
        $this->word = $word;
        $this->failed = $failed;
    }
}