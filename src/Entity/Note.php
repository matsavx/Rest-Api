<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $note_name;

    #[ORM\Column(type: 'string', length: 255)]
    private $note_description;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoteName(): ?string
    {
        return $this->note_name;
    }

    public function setNoteName(string $note_name): self
    {
        $this->note_name = $note_name;

        return $this;
    }

    public function getNoteDescription(): ?string
    {
        return $this->note_description;
    }

    public function setNoteDescription(string $note_description): self
    {
        $this->note_description = $note_description;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
