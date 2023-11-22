<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Entity\Vehicule;


class DashboardController extends AbstractController
{

    private $commandeRepository;
    private $vehiculeRepository;

    public function __construct(CommandeRepository $commandeRepository, VehiculeRepository $vehiculeRepository)
    {
        $this->commandeRepository = $commandeRepository;
        $this->vehiculeRepository = $vehiculeRepository;
    }



    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

/*
public function searchCommandes(Request $request): Response
{
    // Récupération des dates de départ et de fin depuis la requête
    $dateDebut = $request->query->get('date_heure_depart');
    $dateFin = $request->query->get('date_heure_fin');

    // Recherche des commandes dans la plage de dates spécifiée
    $commandes = $this->commandeRepository->findByDateRange($dateDebut, $dateFin);

    // Récupération des véhicules disponibles pendant cette période
    $vehiculesDisponibles = [];
    foreach ($commandes as $commande) {
        $vehicule = $commande->getVehicule();
        // Vérifier si le véhicule est disponible
        if ($this->vehiculeRepository->isDisponiblePendantPeriode($vehicule, $dateDebut, $dateFin)) {
            $vehiculesDisponibles[] = $vehicule;
        }
    }

    // Affichage du contenu dans la console Symfony
    dump($vehiculesDisponibles);

    return $this->render('search/resultats.html.twig', [
        'vehicules_disponibles' => $vehiculesDisponibles,
        'commandes' => $commandes,
        'date_heure_depart' => $dateDebut,
        'date_heure_fin' => $dateFin,
    ]);
}
*/
/*
#[Route('/search_vehicles', name: 'search_vehicles')]
public function searchVehicles(Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupération des dates de début et de fin depuis le formulaire
    $dateDebut = $request->query->get('date_debut');
    $dateFin = $request->query->get('date_fin');

    // Requête pour récupérer les véhicules disponibles entre les deux dates
    $query = $entityManager->createQueryBuilder()
        ->select('v')
        ->from(Vehicule::class, 'v')
        ->leftJoin(Commande::class, 'c', 'WITH', 'c.vehicule = v.id')
        ->where('c.dateHeureDepart > :dateDebut AND c.dateHeureFin < :dateFin')
        ->setParameter('dateDebut', $dateDebut)
        ->setParameter('dateFin', $dateFin)
        ->getQuery();

    $vehicles = $query->getResult();

    return $this->render('search/resultats.html.twig', [
        'vehicles' => $vehicles,
    ]);
}

*/

#[Route('/search_vehicles', name: 'search_vehicules')]
public function searchVehicules(Request $request): Response
{
    $dateDebut = $request->query->get('date_debut');
    $dateFin = $request->query->get('date_fin');

    $vehiculesDisponibles = $this->vehiculeRepository
        ->findVehiculesDisponibles($dateDebut, $dateFin);

    return $this->render('search/resultats.html.twig', [
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
        'vehiculesDisponibles' => $vehiculesDisponibles,
    ]);
}


}
