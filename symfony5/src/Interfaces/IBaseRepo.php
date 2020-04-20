<?php

namespace App\Interfaces;

/**
 * #70 Work with entities inside the repository.
 * - https://github.com/doctrine/orm/blob/master/lib/Doctrine/ORM/EntityRepository.php
 * - https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/#comment-4155200782.
 */
interface IBaseRepo
{
    public function find($id, $lockMode = null, $lockVersion = null);

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);

    public function findOneBy(array $criteria, array $orderBy = []);
}
