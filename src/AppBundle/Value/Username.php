<?php

namespace AppBundle\Value;

class Username {

    private $value;

    public function __construct($value) {

        if (false === \is_string($value)) {
            throw new \InvalidArgumentException('Not a string provided.');
        }
        if (mb_strlen($value) > 10) {
            throw new \InvalidArgumentException('Max username length is 10.');
        }

        if (!preg_match('/^[a-zA-Z0-9\x{0430}-\x{044F}\x{0410}-\x{042F}\s]+$/u', $value)) {
            throw new \InvalidArgumentException('Invalid username format.');
        }

        $this->value = $value;
    }

    public function getValue() {

        return $this->value;
    }

    public function __toString() {

        return $this->value;
    }
}