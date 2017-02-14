<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Translation;

/**
 * TranslationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TranslationRepository extends \Doctrine\ORM\EntityRepository {

    /**
     * @param int $id
     *
     * @return Translation
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslationForWord(int $id) {

        return $this->createQueryBuilder('t')->where('t.word1 = :id')->orWhere('t.word2 = :id')->setParameter('id',
            $id)->getQuery()->getSingleResult();
    }
}