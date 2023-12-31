<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getReferentiels"])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getReferentiels"])]
    private ?string $status = 'en attente';

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    private ?Referentiel $referentiel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getReferentiel(): ?Referentiel
    {
        return $this->referentiel;
    }

    public function setReferentiel(?Referentiel $referentiel): static
    {
        $this->referentiel = $referentiel;

        return $this;
    }
}
