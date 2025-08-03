<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'countries')]
#[ORM\Index(name: "name_idx", columns: ["name"])]
class Country
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 2, unique: true)]
    private string $isoCode;

    public function __construct(string $name = '', string $isoCode = '')
    {
        $this->name = $name;
        $this->isoCode = $isoCode;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId( int $id ): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName( string $name ): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setIsoCode( string $isoCode ): self
    {
        $this->isoCode = $isoCode;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

}