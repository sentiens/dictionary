<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Translation;
use AppBundle\Entity\Word;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadDictionaryData
 * @package AppBundle\DataFixtures\ORM
 */
class LoadDictionaryData implements FixtureInterface {

    public function load(ObjectManager $manager) {

        $rawWords = json_decode('{
            "apple": "яблоко",
            "pear": "персик",
            "orange": "апельсин",
            "grape": "виноград",
            "lemon": "лимон",
            "pineapple": "ананас",
            "watermelon": "арбуз",
            "coconut": "кокос",
            "banana": "банан",
            "pomelo": "помело",
            "strawberry": "клубника",
            "raspberry": "малина",
            "melon": "дыня",
            "apricot": "абрикос",
            "mango": "манго",
            "pear": "слива",
            "pomegranate": "гранат",
            "cherry": "вишня"
        }', true);

        foreach ($rawWords as $strEn => $strRu) {
            $enWord = Word::recordEnglishWord($strEn);
            $manager->persist($enWord);
            $ruWord = Word::recordRussianWord($strRu);
            $manager->persist($ruWord);
            $translation = Translation::translate($enWord, $ruWord);
            $manager->persist($translation);
        }
        $manager->flush();
    }
}