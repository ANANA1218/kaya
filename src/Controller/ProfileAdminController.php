<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileAdminController extends AbstractController
{
    #[Route('/admin/user-details', name: 'user_details')]
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
    
        // Afficher l'ID de l'utilisateur dans une vue
        return $this->render('admin/profile/index.html.twig', [
            'userId' => $userId,
            'userCivilite' => $userCivilite,
            'userPseudo' => $userPseudo,
            'userName' => $userName,
            'userFirstName' => $userFirstName,
        ]);
    }
    
}
