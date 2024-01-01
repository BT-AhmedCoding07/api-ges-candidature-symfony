<?php

namespace App\Entity;

use App\Repository\ReferentielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReferentielRepository::class)]
class Referentiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getReferentiels","getCandidatures"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getReferentiels"])]
    #[Assert\NotBlank(message: "Le libelle du referentiel est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le libelle doit faire au moins {{ limit }} caractères", maxMessage: "Le libelle ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getReferentiels"])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getReferentiels"])]
    #[Assert\NotBlank(message: "L'écheance du referentiel est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "L'écheance doit faire au moins {{ limit }} caractères", maxMessage: "l'écheance ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $echeances = null;

    #[ORM\OneToMany(mappedBy: 'referentiel', targetEntity: Candidature::class)]
    #[Groups(["getReferentiels"])]
    private Collection $candidatures;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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

    public function getEcheances(): ?string
    {
        return $this->echeances;
    }

    public function setEcheances(string $echeances): static
    {
        $this->echeances = $echeances;

        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setReferentiel($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getReferentiel() === $this) {
                $candidature->setReferentiel(null);
            }
        }

        return $this;
    }
}
