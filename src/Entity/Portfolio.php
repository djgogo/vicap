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

use App\Repository\PortfolioRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Index(name: "name_idx", columns: ["name"])]
#[ORM\Entity(repositoryClass: PortfolioRepository::class)]
#[Vich\Uploadable]
class Portfolio
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

    #[ORM\ManyToMany(targetEntity: PortfolioCategory::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'portfolio_portfolio_category')]
    private Collection $portfolioCategories;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $client = null;

    #[ORM\Column(length: 255)]
    private ?string $websiteUrl = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $features = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $technologies = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * Holds temporarily the uploaded file.
     *
     * Not persisted.
     */
    #[Vich\UploadableField(mapping: "portfolios", fileNameProperty: "image")]
    private ?File $imageFile = null;

    /**
     * Holds metadata for photo cropping.
     * This is a serialized JSON string in format: {"x": 0, "y", "width": 0, "height": 0}.
     *
     * Not persisted.
     */
    private ?string $imageCropData = '';

    public function __construct()
    {
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
        $this->portfolioCategories = new ArrayCollection();
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

    /**
     * @return Collection<int, PortfolioCategory>
     */
    public function getPortfolioCategories(): Collection
    {
        return $this->portfolioCategories;
    }

    public function addPortfolioCategory(PortfolioCategory $portfolioCategory): static
    {
        if (!$this->portfolioCategories->contains($portfolioCategory)) {
            $this->portfolioCategories->add($portfolioCategory);
        }

        return $this;
    }

    public function removePortfolioCategory(PortfolioCategory $portfolioCategory): static
    {
        $this->portfolioCategories->removeElement($portfolioCategory);

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

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): Portfolio
    {
        $this->client = $client;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): Portfolio
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): static
    {
        $this->features = $features;
        return $this;
    }

    public function getTechnologies(): ?string
    {
        return $this->technologies;
    }

    public function setTechnologies(?string $technologies): Portfolio
    {
        $this->technologies = $technologies;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage($image): static
    {
        $this->image = $image;
        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setImageFile(File|\Symfony\Component\HttpFoundation\File\UploadedFile $image = null): void
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new DateTime();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageCropData(): string
    {
        return json_decode($this->imageCropData);
    }

    public function setImageCropData($imageCropData): void
    {
        $this->imageCropData = $imageCropData;
    }
}
