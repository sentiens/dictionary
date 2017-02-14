<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Word
 *
 * @ORM\Table(name="word")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WordRepository")
 */
class Word {

    const LANG_RU = 'ru';

    const LANG_EN = 'en';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="word", type="string", length=255)
     */
    private $word;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2)
     */
    private $lang;

    /**
     * @param string $wordStr
     *
     * @return static
     */
    public static function recordEnglishWord(string $wordStr) {

        $word = new Word();

        $word->lang = self::LANG_EN;
        $word->word = $wordStr;

        return $word;
    }

    /**
     * @param string $wordStr
     *
     * @return static
     */
    public static function recordRussianWord(string $wordStr) {

        $word = new Word();

        $word->lang = self::LANG_RU;
        $word->word = $wordStr;

        return $word;
    }

    /**
     * Get id
     * @codeCoverageIgnore
     * @return int
     */
    public function getId() {

        return $this->id;
    }

    /**
     * Get word
     *
     * @return string
     */
    public function getWord() {

        return $this->word;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang() {

        return $this->lang;
    }
}

