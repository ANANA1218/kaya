<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;

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


  /*  public function findVehiculesDisponibles($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.commandes', 'c')
            ->where('v.disponibilite = :disponible')
            ->andWhere(':dateDebut NOT BETWEEN c.dateHeureDepart AND c.dateHeureFin')
            ->andWhere(':dateFin NOT BETWEEN c.dateHeureDepart AND c.dateHeureFin')
            ->andWhere('c.id IS NULL') // Vérifie si le véhicule n'a aucune commande qui se chevauche
            ->setParameter('disponible', true)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();
    }*/
    public function findVehiculesDisponibles($dateDebut, $dateFin)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            "SELECT v
            FROM App\Entity\Vehicule v
            WHERE v.disponibilite = true
            AND NOT EXISTS (
                SELECT 1
                FROM App\Entity\Commande c
                WHERE c.vehicule = v
                AND (
                    (:dateDebut BETWEEN c.dateHeureDepart AND c.dateHeureFin)
                    OR (:dateFin BETWEEN c.dateHeureDepart AND c.dateHeureFin)
                    OR (c.dateHeureDepart BETWEEN :dateDebut AND :dateFin)
                    OR (c.dateHeureFin BETWEEN :dateDebut AND :dateFin)
                )
            )"
        );

        $query->setParameter('dateDebut', $dateDebut);
        $query->setParameter('dateFin', $dateFin);

        return $query->getResult();
    
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
