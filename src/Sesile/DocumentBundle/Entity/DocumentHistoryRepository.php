<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sesile\DocumentBundle\Entity\DocumentHistory;

/**
 * DocumentHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DocumentHistoryRepository extends EntityRepository
{

    public function writeLog($document, $comment, $sha)
    {
        $em = $this->getEntityManager();
        $documentHistory = new DocumentHistory();
        $documentHistory->setDate(new \DateTime("now"));
        $documentHistory->setComment($comment);
        $documentHistory->setSha($sha);
        $documentHistory->setDocument($document);
        $em->persist($documentHistory);
        $em->flush();

    }

    public function getHistory($document){
        $q = $this->createQueryBuilder('e')
            ->where('e.document = :document')
            ->setParameter('document', $document)
            ->orderBy('e.date', 'DESC')
        ->getQuery();
        return $q->getResult();
    }
}



