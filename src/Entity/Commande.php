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

    #[ORM\Column(name: "date_heure_depart", type: "date")]
    private \DateTimeInterface $dateHeureDepart;

    #[ORM\Column(name: "date_heure_fin", type: "date")]
    private \DateTimeInterface $dateHeureFin;

    #[ORM\Column(name: "prix_total", type: "integer")]
    private int $prixTotal;

   
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Vehicule::class, inversedBy: "commandes")]
    #[ORM\JoinColumn(name: "id_vehicule", referencedColumnName: "id", nullable: false)]
    private Vehicule $vehicule;


  /**
 * @ORM\Column(name="date_enregistrement", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
 */
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

    public function getDateDepart(): string
    {
        return $this->dateHeureDepart->format('Y-m-d');
    }
    
    public function setDateDepart(string $dateDepart): self
    {
        $this->dateHeureDepart = \DateTime::createFromFormat('Y-m-d', $dateDepart);
        return $this;
    }
    
    public function getDateFin(): string
    {
        return $this->dateHeureFin->format('Y-m-d');
    }
    
    public function setDateFin(string $dateFin): self
    {
        $this->dateHeureFin = \DateTime::createFromFormat('Y-m-d', $dateFin);
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
