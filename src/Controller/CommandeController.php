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
    
    /**
     * @Route("/reservation/{id}", name="reserver_vehicule")
     */
    public function reserverVehicule(Request $request, $id): Response
    {
        $vehicule = $this->vehiculeRepository->find($id);
    
        $panier = $this->getOrCreatePanier($request);
        $panier['vehicules'][] = $vehicule->getId();
    
        $session = $request->getSession();
        $session->set('panier', $panier);
    
        return $this->redirectToRoute('afficher_panier');
    }
    
      /**
     * @Route("/panier", name="afficher_panier")
     */
    public function afficherPanier(Request $request): Response
{
    $panier = $this->getOrCreatePanier($request);
    $vehiculeRepository = $this->vehiculeRepository;

    $vehiculesPanier = [];
    foreach ($panier['vehicules'] as $vehiculeId) {
        $vehicule = $vehiculeRepository->find($vehiculeId);
        if ($vehicule) {
            $vehiculesPanier[] = $vehicule;
        }
    }

    return $this->render('panier/panier.html.twig', [
        'vehiculesPanier' => $vehiculesPanier,
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

}
