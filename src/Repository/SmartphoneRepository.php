<?php

namespace App\Repository;

use App\Entity\Smartphone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Smartphone>
 *
 * @method Smartphone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Smartphone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Smartphone[]    findAll()
 * @method Smartphone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmartphoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Smartphone::class);
    }

    public function getAllWithPagination(int $page, int  $limit) : array
    {
        $qb = $this->createQueryBuilder('s')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}
