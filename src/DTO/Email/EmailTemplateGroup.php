<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\DTO\Email;

use Doctrine\Common\Collections\ArrayCollection;

class EmailTemplateGroup
{
    public string $name;
    public ArrayCollection $translations;

    public function __construct(string $name = '')
    {
        $this->name = $name;
        $this->translations = new ArrayCollection();
    }

    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    public function setTranslations(ArrayCollection $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}