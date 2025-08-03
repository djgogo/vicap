<?php

namespace App\Entity\User;

use App\Entity\Country;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A user address details.
 * @package App\Entity\User
 *
 */
trait LocationTrait
{
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected ?Country $country = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    protected ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 12, nullable: true)]
    protected ?string $zip = null;

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): static
    {
        $this->zip = $zip;
        return $this;
    }

}