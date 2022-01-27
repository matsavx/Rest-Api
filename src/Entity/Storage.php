<?php

namespace App\Entity;

use App\Repository\StorageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StorageRepository::class)]
class Storage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $storage_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $storage_size;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'storages')]
    private $storage_author;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $storage_real_name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStorageName(): ?string
    {
        return $this->storage_name;
    }

    public function setStorageName(?string $storage_name): self
    {
        $this->storage_name = $storage_name;

        return $this;
    }

    public function getStorageSize(): ?string
    {
        return $this->storage_size;
    }

    public function setStorageSize(?string $storage_size): self
    {
        $this->storage_size = $storage_size;

        return $this;
    }

    public function getStorageAuthor(): ?User
    {
        return $this->storage_author;
    }

    public function setStorageAuthor(?User $storage_author): self
    {
        $this->storage_author = $storage_author;

        return $this;
    }

    public function getStorageRealName(): ?string
    {
        return $this->storage_real_name;
    }

    public function setStorageRealName(?string $storage_real_name): self
    {
        $this->storage_real_name = $storage_real_name;

        return $this;
    }
}
