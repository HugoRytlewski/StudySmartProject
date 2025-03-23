<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, DocumentCommentaire>
     */
    #[ORM\OneToMany(targetEntity: DocumentCommentaire::class, mappedBy: 'Document')]
    private Collection $documentCommentaires;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?User $user = null;

    public function __construct()
    {
        $this->documentCommentaires = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, DocumentCommentaire>
     */
    public function getDocumentCommentaires(): Collection
    {
        return $this->documentCommentaires;
    }

    public function addDocumentCommentaire(DocumentCommentaire $documentCommentaire): static
    {
        if (!$this->documentCommentaires->contains($documentCommentaire)) {
            $this->documentCommentaires->add($documentCommentaire);
            $documentCommentaire->setDocument($this);
        }

        return $this;
    }

    public function removeDocumentCommentaire(DocumentCommentaire $documentCommentaire): static
    {
        if ($this->documentCommentaires->removeElement($documentCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($documentCommentaire->getDocument() === $this) {
                $documentCommentaire->setDocument(null);
            }
        }

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
}
