<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VehiculeRepository; // Assurez-vous d'avoir importé le Repository adéquat
use App\Entity\Vehicule;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommandeRepository; // Assurez-vous d'avoir importé le Repository adéquat
use App\Entity\Commande;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\Component\Pager\PaginatorInterface;

class AdminController extends AbstractController
{
    private $vehiculeRepository;
    private $userRepository;
    private $commandeRepository;

    public function __construct(
        VehiculeRepository $vehiculeRepository,
        UserRepository $userRepository,
        CommandeRepository $commandeRepository
    ) {
        $this->vehiculeRepository = $vehiculeRepository;
        $this->userRepository = $userRepository;
        $this->commandeRepository = $commandeRepository;
    }

    /**
     * @Route("/admin", name="admin_home")
     */
    public function adminHome(): Response
    {
        return $this->render('admin/home.html.twig');
    }


    // vehicules 
 /**
     * @Route("/admin/vehicules", name="vehicules_list")
     */
    public function vehiculesList(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->vehiculeRepository->createQueryBuilder('v');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Requête pour sélectionner les véhicules
            $request->query->getInt('page', 1), // Paramètre de la page en cours
            10 // Nombre d'éléments par page
        );
    
        return $this->render('admin/vehicules_list.html.twig', [
            'pagination' => $pagination,
        ]);
    }




/**
     * @Route("/admin/vehicule/{id}", name="show_vehicule")
     */
    public function showVehiculeByID($id): Response
    {
        $vehicule = $this->vehiculeRepository->find($id);

        return $this->render('admin/vehicule_by_id.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }




    private function moveAndUpdateImage($request , $vehicule) :void{
        // récupérer l'image 
        $file = $request->files->get("form")["photo"] ?? null;

        if ($file !== null) {
            // déplacer dans le dossier upload
            // le dossier où stocker l'image 
            $uploadDirectory = $this->getParameter("upload_directory");
            $nomFichier = md5(uniqid()) . "." . $file->guessExtension();
            
            $file->move($uploadDirectory, $nomFichier);
            
            // enregistrer en base de données 
            // url relatif de l'image 
            $vehicule->setPhoto($nomFichier);
        }
    }


 /**
     * @Route("/vehicules/create", name="create_vehicule")
     */
    public function createVehicule(Request $request, EntityManagerInterface $entityManager): Response
{
    // Création d'une nouvelle instance de Vehicule
    $vehicule = new Vehicule();

    // Création du formulaire
    $form = $this->createFormBuilder($vehicule)
        ->add('titre', TextType::class)
        ->add('marque', TextType::class)
        ->add('modele', TextType::class)
        ->add('description', TextareaType::class)
        ->add('photo', FileType::class)
        ->add('prixJournalier', NumberType::class)
        ->add('save', SubmitType::class, ['label' => 'Ajouter'])
        ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupération des données du formulaire
        $vehicule = $form->getData();
        $this->moveAndUpdateImage($request , $vehicule);
        // Enregistrement du véhicule dans la base de données
        $entityManager->persist($vehicule);
        $entityManager->flush();

        $this->addFlash('success', 'Véhicule ajouté avec succès !');

        // Redirection vers une autre page après l'ajout
        return $this->redirectToRoute('vehicules_list');
    }

    // Rendre la vue du formulaire
    return $this->render('admin/Form/vehicule/create.html.twig', [
        'form' => $form->createView(),
    ]);
}


/**
 * @Route("/admin/vehicule/{id}/update", name="update_vehicule")
 * @ParamConverter("vehicule", class="App\Entity\Vehicule")
 */
public function updateVehicule(Request $request, EntityManagerInterface $entityManager, Vehicule $vehicule): Response
{
    // Création du formulaire pré-rempli avec les données du véhicule à mettre à jour
    $form = $this->createFormBuilder($vehicule)
    ->add('titre')
    ->add('marque')
    ->add('modele')
    ->add('description')
    ->add('photo', FileType::class, [
        'mapped' => false, // Ceci est important pour accepter les fichiers
        'required' => false, // Permet de ne pas rendre obligatoire le champ photo
    ])
    ->add('prixJournalier')
    ->add('save', SubmitType::class, ['label' => 'Modifier'])
    ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Convertir la photo en un objet UploadedFile
        $photoFile = $form->get('photo')->getData();

        // Gérer la photo uniquement si elle est modifiée
        if ($photoFile instanceof UploadedFile) {
            // Enregistrer la nouvelle photo
            $newFilename = md5(uniqid()) . '.' . $photoFile->guessExtension();
            $photoFile->move(
                $this->getParameter('upload_directory'),
                $newFilename
            );

            // Mettre à jour l'entité Vehicule avec le nouveau nom de fichier
            $vehicule->setPhoto($newFilename);
        }

        // Enregistrer les autres modifications dans la base de données
        $entityManager->flush();

        $this->addFlash('success', 'Véhicule mis à jour avec succès !');

        // Redirection vers une autre page après la mise à jour
        return $this->redirectToRoute('vehicules_list');
    }

    // Rendre la vue du formulaire
    return $this->render('admin/Form/vehicule/update.html.twig', [
        'form' => $form->createView(),
    ]);
}


/**
 * @Route("/admin/vehicule/{id}/delete", name="delete_vehicule")
 */
public function deleteVehicule(Request $request, EntityManagerInterface $entityManager, Vehicule $vehicule): Response
{
    $entityManager->remove($vehicule);
    $entityManager->flush();

    $this->addFlash('success', 'Véhicule supprimé avec succès !');

    return $this->redirectToRoute('vehicules_list');
}




//user 



//ajouter un create , un delete, update , un viewByID

  /**
     * @Route("/admin/users", name="users_list")
     */
    public function usersList(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/users_list.html.twig', [
            'users' => $users,
        ]);
    }
   



    /**
     * @Route("/user/{id}", name="show_users")
     */
    public function showUser($id): Response
    {
        $user = $this->userRepository->find($id);

        return $this->render('admin/users_by_id.html.twig', [
            'user' => $user,
        ]);
    }



     #[Route('/admin/user/create', name: 'create_users')]
    public function createUser(Request $request, EntityManagerInterface $entityManager): Response
{
    // Création d'une nouvelle instance de Vehicule
    $user = new User();

    // Création du formulaire
    $form = $this->createFormBuilder($user)
        ->add('pseudo', TextType::class)
        ->add('password', TextType::class)
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add('email', TextType::class)
        ->add('civilite', ChoiceType::class, [
            'choices' => [
                'M.' => 'Homme',
                'Mme' => 'Femme',
            ],
        ])
        
        ->add('save', SubmitType::class, ['label' => 'Ajouter'])
        ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupération des données du formulaire
        $user = $form->getData();
        $user->setRoles(['ROLE_USER']);
        // Enregistrement du véhicule dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', '$user ajouté avec succès !');

        // Redirection vers une autre page après l'ajout
        return $this->redirectToRoute('users_list');
    }

    // Rendre la vue du formulaire
    return $this->render('admin/Form/user/create.html.twig', [
        'form' => $form->createView(),
    ]);
}


/**
 * @Route("/admin/user/{id}/update", name="update_users")
 */
public function updateUser(Request $request, EntityManagerInterface $entityManager, User $user): Response
{
    // Création du formulaire pré-rempli avec les données du véhicule à mettre à jour
        $form = $this->createFormBuilder($user)
        ->add('pseudo', TextType::class)
        ->add('password', TextType::class)
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add('email', TextType::class)
        ->add('civilite', ChoiceType::class, [
            'choices' => [
                'M.' => 'Homme',
                'Mme' => 'Femme',
            ],
        ])
        
        ->add('save', SubmitType::class, ['label' => 'Modifier'])
        ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrement automatique des modifications dans la base de données via Doctrine
        $entityManager->flush();

        $this->addFlash('success', 'Membre mis à jour avec succès !');
        $user->setRoles(['ROLE_USER']);
        // Redirection vers une autre page après la mise à jour
        return $this->redirectToRoute('users_list');
    }

    // Rendre la vue du formulaire
    return $this->render('admin/Form/user/update.html.twig', [
        'form' => $form->createView(),
    ]);
}


/**
 * @Route("/admin/user/{id}/delete", name="delete_users")
 */
public function deleteUser(Request $request, EntityManagerInterface $entityManager, User $user): Response
{
    $entityManager->remove($user);
    $entityManager->flush();

    $this->addFlash('success', 'Membre supprimé avec succès !');

    return $this->redirectToRoute('users_list');
}





//ajouter  un delete, update , un viewByID

 /**
     * @Route("/admin/orders", name="orders_list")
     */
    public function ordersList(Request $request, PaginatorInterface $paginator): Response
    {
       
        $queryBuilder = $this->commandeRepository->createQueryBuilder('v');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Requête pour sélectionner les véhicules
            $request->query->getInt('page', 1), // Paramètre de la page en cours
            10 // Nombre d'éléments par page
        );
    
        return $this->render('admin/orders_list.html.twig', [
            'pagination' => $pagination,
        ]);
       
    }
  
    /**
     * @Route("/commande/{id}", name="show_commande")
     */
    public function showCommmande($id): Response
    {
        $commande = $this->commandeRepository->find($id);

        return $this->render('admin/orders_by_id.html.twig', [
            'commande' => $commande,
        ]);
    }







    /**
     * @Route("/admin/commande/create", name="create_commande")
     */

    public function createCommande(Request $request, EntityManagerInterface $entityManager): Response
    {

        $userRepository = $entityManager->getRepository(User::class);
        $listeMembres = $userRepository->findAll(); 


        $vehiculesRepository = $entityManager->getRepository(Vehicule::class);
        $listeIdVehicules = $vehiculesRepository->findAll(); 

        // Création d'une nouvelle instance de Commande
        $commande = new Commande();
    
        // Création du formulaire pour créer une nouvelle commande
        $form = $this->createFormBuilder($commande)
        ->add('user', ChoiceType::class, [
            'choices' => $listeMembres,
            'choice_label' => function ($user) {
                return $user->getId(); 
            },
            'label' => 'ID user'
        ])

        ->add('vehicule', ChoiceType::class, [
            'choices' => $listeIdVehicules,
            'choice_label' => function ($vehicule) {
                return $vehicule->getId(); 
            },
            'label' => 'ID vehicule'
        ])
            ->add('date_heure_depart', DateTimeType::class)
            ->add('date_heure_fin', DateTimeType::class)
            ->add('prix_total', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement automatique de la nouvelle commande dans la base de données via Doctrine
            $entityManager->persist($commande);
            $entityManager->flush();
    
            $this->addFlash('success', 'Nouvelle commande créée avec succès !');
    
            // Redirection vers une autre page après la création
            return $this->redirectToRoute('orders_list');
        }
    
        // Rendre la vue du formulaire de création
        return $this->render('admin/Form/order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    
// Modifier la route pour l'action update
/**
 * @Route("/admin/commande/{id}/update", name="update_commande")
 
*/
public function updateCommande(Request $request, EntityManagerInterface $entityManager, Commande $commande): Response
{


       $userRepository = $entityManager->getRepository(User::class);
        $listeMembres = $userRepository->findAll(); 


        $vehiculesRepository = $entityManager->getRepository(Vehicule::class);
        $listeIdVehicules = $vehiculesRepository->findAll(); 


    // Création du formulaire pré-rempli avec les données du véhicule à mettre à jour
    $form = $this->createFormBuilder($commande)
    ->add('user', ChoiceType::class, [
        'choices' => $listeMembres,
        'choice_label' => function ($user) {
            return $user->getId(); 
        },
        'label' => 'ID user'
    ])

    ->add('vehicule', ChoiceType::class, [
        'choices' => $listeIdVehicules,
        'choice_label' => function ($vehicule) {
            return $vehicule->getId(); 
        },
        'label' => 'ID vehicule'
    ])
        ->add('date_heure_depart', DateTimeType::class)
        ->add('date_heure_fin', DateTimeType::class)
        ->add('prix_total', TextType::class)
        ->add('save', SubmitType::class, ['label' => 'Modifier'])
        ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrement automatique des modifications dans la base de données via Doctrine
        $entityManager->flush();

        $this->addFlash('success', 'Commande mis à jour avec succès !');

        // Redirection vers une autre page après la mise à jour
        return $this->redirectToRoute('orders_list');
    }

    // Rendre la vue du formulaire
    return $this->render('admin/Form/order/update.html.twig', [
        'form' => $form->createView(),
    ]);
}




/**
 * @Route("/admin/commande/{id}/delete", name="delete_commande")
 */
public function deleteCommande(Request $request, EntityManagerInterface $entityManager, Commande $commande): Response
{
    $entityManager->remove($commande);
    $entityManager->flush();

    $this->addFlash('success', 'Commande supprimé avec succès !');

    return $this->redirectToRoute('orders_list');
}






}


