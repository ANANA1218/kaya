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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use DateTime;

class CommandeController extends AbstractController
{

    private $commandeRepository;
    private $vehiculeRepository;

    public function __construct(CommandeRepository $commandeRepository, VehiculeRepository $vehiculeRepository)
    {
        $this->commandeRepository = $commandeRepository;
        $this->vehiculeRepository = $vehiculeRepository;
    }



    private function getOrCreatePanier(Request $request): array
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);
    
        if (!isset($panier['vehicules'])) {
            $panier['vehicules'] = [];
        }
    
        return $panier;
    }
    




    #[Route("/reservation/{id}", name:"reserver_vehicule")]
    public function reserverVehicule(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        // ... (récupération du véhicule par son ID)
        $vehicule = $this->vehiculeRepository->find($id);

        // Récupération des dates de début et fin de la requête POST
        $dateDebut = $request->request->get('date_debut');
        $dateFin = $request->request->get('date_fin');

        // Ajout du véhicule au panier avec les dates de réservation
        $panier = $this->getOrCreatePanier($request);
        $panier['vehicules'][] = [
            'vehiculeId' => $vehicule->getId(),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ];
        $request->getSession()->set('panier', $panier);

        // Redirection vers la page du panier
        return $this->redirectToRoute('afficher_panier');
    }
    
     /**
 * @Route("/panier", name="afficher_panier")
 */
public function afficherPanier(Request $request): Response
{
    $dateDebut = $request->query->get('date_debut');
    $dateFin = $request->query->get('date_fin');
dump($dateDebut, $dateFin);

    $panier = $this->getOrCreatePanier($request);
    $vehiculeRepository = $this->vehiculeRepository;

    $vehiculesPanier = [];
    foreach ($panier['vehicules'] as $vehiculeId) {
        $vehicule = $vehiculeRepository->find($vehiculeId);
        if ($vehicule) {
            $vehiculesPanier[] = $vehicule;
        }
    }

    $user = $this->getUser();
    $userName = $user ? $user->getNom() : null;
    $userFirstName = $user ? $user->getPrenom() : null;
    $userId = $user ? $user->getUserIdentifier() : null;

    return $this->render('panier/panier.html.twig', [
        'vehiculesPanier' => $vehiculesPanier,
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
        'userName' => $userName,
        'userFirstName' => $userFirstName,
        'userId' => $userId,
    ]);
}





public function supprimerVehiculePanier(Request $request, $id): Response
{
    $vehiculeId = (int) $id;

    $session = $request->getSession();
    $panier = $session->get('panier', []);

    // Trouver l'index du véhicule dans le panier
    $index = array_search($vehiculeId, $panier['vehicules']);

    if ($index !== false) {
        // Retirer le véhicule du panier
        unset($panier['vehicules'][$index]);
        $session->set('panier', $panier);
    }

    return $this->redirectToRoute('afficher_panier');
}


 /**
     * @Route("/synthese_reservation/{id_vehicule}", name="synthese_reservation")
     */
    public function syntheseReservation(Request $request, $id_vehicule): Response
    {
        // Récupération du véhicule depuis la base de données
        $vehicule = $this->vehiculeRepository->find($id_vehicule);

        // Récupération des dates de début et de fin depuis la requête
        $dateDebut = new DateTime($request->query->get('date_debut'));
        $dateFin = new DateTime($request->query->get('date_fin'));

        $dateDebutFormatted = $dateDebut->format('Y-m-d'); // Changez le format selon vos besoins
        $dateFinFormatted = $dateFin->format('Y-m-d');

        // Affichage de la page de résumé avec les détails
        return $this->render('panier/panier.html.twig', [
            'vehicule' => $vehicule,
            'dateDebut' => $dateDebutFormatted,
            'dateFin' => $dateFinFormatted,
        ]);
    }





}
