<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

trait ProfileTrait
{
    #[ORM\Column(name: "first_name", type: "string", length: 254, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: "last_name", type: "string", length: 254, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(name: "about", type: "text", nullable: true)]
    private ?string $about = null;

    #[ORM\Column(name: "birthdate", type: "date", nullable: true)]
    private ?DateTime $birthdate = null;

    #[ORM\Column(name: "company", type: "string", length: 254, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $websiteUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $xUrl = null;

    #[ORM\Column(type: "string", length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $jobDesignation = null;

    /**
     * Holds temporarily the uploaded file.
     *
     * Not persisted.
     */
    #[Vich\UploadableField(mapping: "avatars", fileNameProperty: "photo")]
    private ?File $photoFile = null;

    /**
     * Holds metadata for photo cropping.
     * This is a serialized JSON string in format: {"x": 0, "y", "width": 0, "height": 0}.
     *
     * Not persisted.
     */
    private ?string $photoCropData = '';

    public function displayName(): ?string
    {
        if ($this->firstName && $this->lastName) {
            return "{$this->firstName} {$this->lastName}";
        }

        return $this->email;
    }

    /**
     * Returns a textural avatar.
     * It could be first letters of first and/or last name or of email.
     */
    public function textAvatar()
    {
        $parts = [];
        if ($this->firstName) {
            $parts[] = $this->firstName[0];
        }
        if ($this->lastName) {
            $parts[] = $this->lastName[0];
        }
        if (count($parts) == 0) {
            $parts[] = $this->email[0];
        }
        return strtoupper(join('', $parts));
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setPhotoFile(File|\Symfony\Component\HttpFoundation\File\UploadedFile $image = null): void
    {
        $this->photoFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new DateTime();
        }
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto($photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhotoCropData()
    {
        return json_decode($this->photoCropData);
    }

    public function setPhotoCropData($photoCropData): void
    {
        $this->photoCropData = $photoCropData;
    }

    public function getBirthdate(): ?DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(DateTime $birthdate = null): self
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;
        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(string $about): self
    {
        $this->about = $about;
        return $this;
    }

    public function getInitials(): string
    {
        return substr($this->getFirstName(), 0, 1) . substr($this->getLastName(), 0, 1);
    }


    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): self
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): self
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getXUrl(): ?string
    {
        return $this->xUrl;
    }

    public function setXUrl(?string $xUrl): self
    {
        $this->xUrl = $xUrl;

        return $this;
    }

    public function getJobDesignation(): ?string
    {
        return $this->jobDesignation;
    }

    public function setJobDesignation(?string $jobDesignation): self
    {
        $this->jobDesignation = $jobDesignation;

        return $this;
    }
}