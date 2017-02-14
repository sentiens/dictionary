<?php

namespace Tests\Infrastructure\Repository;

use AppBundle\Entity\Word;
use Doctrine\ORM\NoResultException;
use Tests\DBTestCase;

class WordRepositoryTest extends DBTestCase {

    private $repository;

    private $words;

    protected function setUp()
    {
        parent::setUp();

        $this->repository = static::$kernel->getContainer()
            ->get('app.service.word_repository');

        $this->words = [
            Word::recordRussianWord('яблоко'),
            Word::recordRussianWord('волк'),
            Word::recordRussianWord('музыка'),
            Word::recordEnglishWord('apple')
        ];

        foreach ($this->words as $word) {
            $this->em->persist($word);
        }

        $this->em->flush();

        foreach ($this->words as $word) {
            $this->em->detach($word);
        }
    }


    public function test_find()
    {
        $word = Word::recordRussianWord('яблоко');

        $this->em->persist($word);
        $this->em->flush();

        $this->em->detach($word);

        $word = $this->repository->find($word->getId());

        $this->assertNotNull($word);
    }

    public function test_findRandomWord()
    {
        $word = $this->repository->findRandomWord();

        $this->assertInstanceOf(Word::class, $word);
    }

    public function test_findRandomWord_by_lang()
    {
        $word = $this->repository->findRandomWord([], Word::LANG_RU);

        $this->assertEquals(Word::LANG_RU, $word->getLang());
        $this->assertInstanceOf(Word::class, $word);
    }

    public function test_findRandomWord_numberOfWords()
    {
        $words = $this->repository->findRandomWord([], null, 3);

        $this->assertCount(3, $words);
    }

    public function test_findRandomWord_excludedIds()
    {
        $this->expectException(NoResultException::class);
        $this->repository->findRandomWord(array_map(function (Word $word) {
            return $word->getId();
        }, $this->words));
    }

    public function test_findRandomWord_all_params()
    {
        $words = $this->repository->findRandomWord([$this->words[0]->getId()], Word::LANG_RU, 3);
    }
}