<?php

namespace App\Repository;

use App\Entity\Plat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Plat>
 *
 * @method Plat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plat[]    findAll()
 * @method Plat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlatRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Plat::class);
        $this->logger = $logger;
    }

    /**
     * Retourne les plats filtrés par allergènes et catégories.
     */
    public function findFilteredPlats(array $allergies, array $categories)
    {
        $this->logger->info('Allergies: ' . implode(', ', $allergies));
        $this->logger->info('Categories: ' . implode(', ', $categories));

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.allergene', 'a')
            ->leftJoin('p.categorie', 'c')
            ->where('a.nom IS NULL OR a.nom NOT IN (:allergies)')
            ->andWhere('c.nom IN (:categories)')
            ->setParameter('allergies', $allergies)
            ->setParameter('categories', $categories);

        $query = $qb->getQuery();
        $this->logger->info('SQL: ' . $query->getSQL());
        $this->logger->info('Parameters: ' . json_encode($query->getParameters()));

        return $query->getResult();
    }
}

