<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Exception\LogicException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Translation
 *
 * @ORM\Table(name="translation", uniqueConstraints={@ORM\UniqueConstraint(name="pair_unique", columns={"word1_id", "word2_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TranslationRepository")
 */
class Translation {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word")
     */
    private $word1;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word")
     */
    private $word2;

    public static function translate(Word $word1, Word $word2) {

        if ($word1->getLang() === $word2->getLang()) {
            throw new LogicException('Word cannot be translated in same language.');
        }

        if (strcmp($word1->getWord(), $word2->getWord()) < 0) {
            list($word1, $word2) = [$word2, $word1];
        }

        $translation = new Translation();

        $translation->word1 = $word1;
        $translation->word2 = $word2;

        return $translation;
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
     * @return Word
     */
    public function getWord1() {

        return $this->word1;
    }

    /**
     * @return Word
     */
    public function getWord2() {

        return $this->word2;
    }

    /**
     * @param Word $word
     *
     * @return null|Word
     */
    public function getTranslation(Word $word) {

        if ($word === $this->word1) {
            return $this->word2;
        } else {
            if ($word === $this->word2) {
                return $this->word1;
            } else {
                return null;
            }
        }
    }
}

