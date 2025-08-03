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

use App\Repository\TradeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Index(name: "name_idx", columns: ["name"])]
#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
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

    #[ORM\ManyToOne(targetEntity: TradeCategory::class, inversedBy: 'trades')]
    private ?TradeCategory $tradeCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $lead = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isMultiConsultant = null;

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

    public function getTradeCategory(): ?TradeCategory
    {
        return $this->tradeCategory;
    }

    public function setTradeCategory(?TradeCategory $tradeCategory): static
    {
        $this->tradeCategory = $tradeCategory;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setLead(?string $lead): static
    {
        $this->lead = $lead;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isMultiConsultant(): ?bool
    {
        return $this->isMultiConsultant;
    }

    public function setIsMultiConsultant(bool $isMultiConsultant): static
    {
        $this->isMultiConsultant = $isMultiConsultant;

        return $this;
    }
}
