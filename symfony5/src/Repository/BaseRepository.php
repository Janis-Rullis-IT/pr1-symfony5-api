<?php

namespace App\Repository;

use App\Interfaces\IBaseRepo;
use Doctrine\ORM\EntityManagerInterface;

/**
 * #70 Work with entities inside the repository.
 * "By extending from the ServiceEntityRepository we are providing all the Doctrine methods to our clients.
 * As followers of best practices and being strict in what we allow our clients to do".
 *
 * - https://github.com/doctrine/orm/blob/master/lib/Doctrine/ORM/EntityRepository.php
 * - https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/#comment-4155200782
 */
class BaseRepository implements IBaseRepo
{
    protected $em;
    protected $entityName;

    public function __construct(EntityManagerInterface $em, string $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->em->find($this->entityName, $id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        $persister = $this->em->getUnitOfWork()->getEntityPersister($this->entityName);

        return $persister->loadAll($criteria, $orderBy ?? [], $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = [])
    {
        $persister = $this->em->getUnitOfWork()->getEntityPersister($this->entityName);

        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
    }
}
