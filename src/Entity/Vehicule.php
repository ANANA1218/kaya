<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?int $prix_journalier = null;

    #[ORM\Column(type: 'boolean')]
    private bool $disponibilite = true; // Par défaut, le véhicule est disponible


   /**
 * @ORM\Column(name="date_enregistrement", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
 */
    private \DateTimeInterface $dateEnregistrement;

/**
     * @ORM\OneToMany(targetEntity=Commande::class, mappedBy="vehicule")
     */
    private $commandes;

    public function __construct()
    {
        $this->dateEnregistrement = new \DateTime(); // Date d'enregistrement définie lors de la création de la commande
        $this->commandes = new ArrayCollection();
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





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }
    
    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPrixJournalier(): ?int
    {
        return $this->prix_journalier;
    }

    public function setPrixJournalier(?int $prix_journalier): static
    {
        $this->prix_journalier = $prix_journalier;

        return $this;
    }

    public function getDisponibilite(): bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }


    public function getCommandes(): Collection
    {
        return $this->commandes;
    }



    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->setVehicule($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->contains($commande)) {
            $this->commandes->removeElement($commande);
            // Vous n'avez pas besoin de définir le côté inverse à null ici
    
            // Si vous avez un code ici pour gérer la relation inverse, vous pouvez l'ajouter
        }
    
        return $this;
    }
    


    
}
