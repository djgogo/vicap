<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\AdminOptionRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: "admin_options")]
#[ORM\Entity(repositoryClass: AdminOptionRepository::class)]
class AdminOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $created;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updated;

    #[ORM\Column(length: 255)]
    private ?string $adminEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salesEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $companyAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyPhone = null;

    public function __construct()
    {
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    public function setUpdated(\DateTime $updated): static
    {
        $this->updated = $updated;

        return $this;
    }

    public function getAdminEmail(): ?string
    {
        return $this->adminEmail;
    }

    public function setAdminEmail(string $adminEmail): static
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    public function getSalesEmail(): ?string
    {
        return $this->salesEmail;
    }

    public function setSalesEmail(?string $salesEmail): static
    {
        $this->salesEmail = $salesEmail;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(?string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(?string $companyPhone): static
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }
}
