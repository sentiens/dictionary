<?php

namespace Tests\Infrastructure\Repository;

use AppBundle\Entity\Translation;
use AppBundle\Entity\Word;
use Tests\DBTestCase;

class TranslationRepositoryTest extends DBTestCase {

    public function test_findTranslationForWord()
    {
        foreach ([
             $enWord = Word::recordEnglishWord('apple'),
             $ruWord = Word::recordRussianWord('яблоко'),
             $translation = Translation::translate($enWord, $ruWord)
         ] as $e) {
            $this->em->persist($e);
        }
        $this->em->flush();

        $repository = static::$kernel->getContainer()->get('app.service.translation_repository');

        /**
         * @var Translation $translation
         */
        $translation = $repository->findTranslationForWord($enWord->getId());

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals($ruWord, $translation->getTranslation($enWord));
    }
}