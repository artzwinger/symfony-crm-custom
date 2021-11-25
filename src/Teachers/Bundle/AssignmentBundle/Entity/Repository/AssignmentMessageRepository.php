<?php

namespace Teachers\Bundle\AssignmentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AssignmentMessageRepository extends EntityRepository
{
    /**
     * Accepts an array of email_imap_id and returns only those that have an assignment message related
     * @param array $emailImapIds
     * @return array|null
     */
    public function filterEmailImapIds(array $emailImapIds): ?array
    {
        $qb = $this->createQueryBuilder('msg')
            ->select('IDENTITY(msg.emailImap)')
            ->where('IDENTITY(msg.emailImap) in (:emailImapIds)')
            ->groupBy('msg.emailImap');
        $result = $qb->setParameter('emailImapIds', $emailImapIds)
            ->getQuery()
            ->getArrayResult();
        $ids = [];
        foreach ($result as $res) {
            $ids[] = current($res);
        }

        return $ids;
    }
}
