<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime as ConstraintsDateTime;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $senderID;

    #[ORM\Column(type: 'integer')]
    private $receiverID;

    #[ORM\Column(type: 'string', length: 20)]
    private $header;

    #[ORM\Column(type: 'string', length: 2000)]
    private $body;

    #[ORM\Column(type: 'datetime')]
    private $timestamp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderID(): ?int
    {
        return $this->senderID;
    }

    public function setSenderID(int $senderID): self
    {
        $this->senderID = $senderID;

        return $this;
    }

    public function getReceiverID(): ?int
    {
        return $this->receiverID;
    }

    public function setReceiverID(int $receiverID): self
    {
        $this->receiverID = $receiverID;

        return $this;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

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
