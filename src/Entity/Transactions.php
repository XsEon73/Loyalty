<?php

namespace App\Entity;

use App\Repository\TransactionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionsRepository::class)]
class Transactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Balance $balance = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $timestamp = null;

    #[ORM\Column]
    private ?int $changes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $type_change = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    public function setBalance(?Balance $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getChanges(): ?int
    {
        return $this->changes;
    }

    public function setChanges(int $changes): static
    {
        $this->changes = $changes;

        return $this;
    }

    public function getTypeChange(): ?string
    {
        return $this->type_change;
    }

    public function setTypeChange(?string $type_change): static
    {
        $this->type_change = $type_change;

        return $this;
    }
}
