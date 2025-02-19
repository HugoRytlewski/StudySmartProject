<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomDuFichier = null;

    #[ORM\Column(length: 255)]
    private ?string $chemin = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDuFichier(): ?string
    {
        return $this->nomDuFichier;
    }

    public function setNomDuFichier(string $nomDuFichier): static
    {
        $this->nomDuFichier = $nomDuFichier;

        return $this;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(string $chemin): static
    {
        $this->chemin = $chemin;

        return $this;
    }

    public function getUploadAt(): ?\DateTimeImmutable
    {
        return $this->uploadAt;
    }

    public function setUploadAt(\DateTimeImmutable $uploadAt): static
    {
        $this->uploadAt = $uploadAt;

        return $this;
    }
}
