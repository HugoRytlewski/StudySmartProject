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

    /**
     * @var Collection<int, Annotation>
     */
    #[ORM\OneToMany(targetEntity: Annotation::class, mappedBy: 'document')]
    private Collection $annotations;

    public function __construct()
    {
        $this->documentCommentaires = new ArrayCollection();
        $this->annotations = new ArrayCollection();
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

    /**
     * @return Collection<int, Annotation>
     */
    public function getAnnotations(): Collection
    {
        return $this->annotations;
    }

    public function addAnnotation(Annotation $annotation): static
    {
        if (!$this->annotations->contains($annotation)) {
            $this->annotations->add($annotation);
            $annotation->setDocument($this);
        }

        return $this;
    }

    public function removeAnnotation(Annotation $annotation): static
    {
        if ($this->annotations->removeElement($annotation)) {
            // set the owning side to null (unless already changed)
            if ($annotation->getDocument() === $this) {
                $annotation->setDocument(null);
            }
        }

        return $this;
    }
}
