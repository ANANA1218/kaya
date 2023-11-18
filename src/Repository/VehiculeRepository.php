<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;


/**
 * @extends ServiceEntityRepository<Vehicule>
 *
 * @method Vehicule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicule[]    findAll()
 * @method Vehicule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }


    public function save(Vehicule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vehicule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findAvailableVehicles(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        // Construire une requête pour trouver les véhicules disponibles
        $qb = $this->createQueryBuilder('v');
        $qb->leftJoin('v.commandes', 'c')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->isNull('c.id'), // Vérifie si le véhicule n'a pas de commande associée
                    $qb->expr()->orX(
                        $qb->expr()->gt('c.date_heure_fin', ':start_date'), // Vérifie si la commande se termine après la date de début
                        $qb->expr()->lt('c.date_heure_depart', ':end_date') // Vérifie si la commande commence avant la date de fin
                    )
                )
            )
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate);

        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Vehicule[] Returns an array of Vehicule objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vehicule
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
