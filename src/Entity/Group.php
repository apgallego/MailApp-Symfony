<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'json')]
    private $userIDs = [];

    #[ORM\Column(type: 'string', length: 30)]
    private $alias;

    #[ORM\Column(type: 'datetime')]
    private $timestamp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIDs(): ?array
    {
        return $this->userIDs;
    }

    public function setUserIDs(array $userIDs): self
    {
        $this->userIDs = $userIDs;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
