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

    private $membreRepository;
    private $commandeRepository;



    public function __construct(VehiculeRepository $vehiculeRepository, CommandeRepository $commandeRepository)
    {
        $this->vehiculeRepository = $vehiculeRepository;
       
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
     * @Route("/admin/vehicule/create", name="create_vehicule")
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








}


