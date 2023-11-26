<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;
use App\Repository\CommandeRepository;

class ProfileAdminController extends AbstractController
{



    private $commandeRepository;
    private $vehiculeRepository;

  

    public function __construct(CommandeRepository $commandeRepository)
    {
        $this->commandeRepository = $commandeRepository;
       
    }





    #[Route('/user-details', name: 'user_details')]
    public function userDetails(): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();
    
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            // Gérer le cas où aucun utilisateur n'est connecté
            throw $this->createNotFoundException('No user found');
        }
    
        // Récupérer l'identifiant de l'utilisateur connecté
        $userId = $user->getUserIdentifier();
        $userCivilite = $user->isCivilite();
        $userPseudo = $user->getPseudo();
        $userName = $user->getNom();
        $userFirstName = $user->getPrenom();

        $userCommands = $this->commandeRepository->findBy(['user' => $user]);
    
        // Afficher l'ID de l'utilisateur dans une vue
        return $this->render('admin/profile/index.html.twig', [
            'userId' => $userId,
            'userCivilite' => $userCivilite,
            'userPseudo' => $userPseudo,
            'userName' => $userName,
            'userFirstName' => $userFirstName,
            'userCommands' => $userCommands,
        ]);
    }
    
}
