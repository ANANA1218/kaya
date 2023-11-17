<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use App\Entity\Vehicule;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "date_heure_depart", type: "datetime")]
    private \DateTimeInterface $dateHeureDepart;

    #[ORM\Column(name: "date_heure_fin", type: "datetime")]
    private \DateTimeInterface $dateHeureFin;

    #[ORM\Column(name: "prix_total", type: "integer")]
    private int $prixTotal;

   
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Vehicule::class)]
    #[ORM\JoinColumn(name: "id_vehicule", referencedColumnName: "id", nullable: false)]
    private Vehicule $vehicule;


    #[ORM\Column(name: "date_enregistrement", type: "datetime")]
    private \DateTimeInterface $dateEnregistrement;


    public function __construct()
    {
        $this->dateEnregistrement = new \DateTime(); // Date d'enregistrement définie lors de la création de la commande
    }

    
    public function getDateEnregistrement(): \DateTimeInterface
    {
        return $this->dateEnregistrement;
    }

    public function setDateEnregistrement(\DateTimeInterface $dateEnregistrement): self
    {
        $this->dateEnregistrement = $dateEnregistrement;
        return $this;
    }



    // Les getters et setters pour les nouveaux champs

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeureDepart(): \DateTimeInterface
    {
        return $this->dateHeureDepart;
    }

    public function setDateHeureDepart(\DateTimeInterface $dateHeureDepart): self
    {
        $this->dateHeureDepart = $dateHeureDepart;
        return $this;
    }

    public function getDateHeureFin(): \DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeInterface $dateHeureFin): self
    {
        $this->dateHeureFin = $dateHeureFin;
        return $this;
    }

    public function getPrixTotal(): int
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(int $prixTotal): self
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }



    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getVehicule(): Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(Vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;
        return $this;
    }


    
}
