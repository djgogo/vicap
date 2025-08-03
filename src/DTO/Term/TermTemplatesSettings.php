<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\DTO\Term;

use Doctrine\Common\Collections\ArrayCollection;

class TermTemplatesSettings
{
    public ArrayCollection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    public function setGroups(ArrayCollection $groups): self
    {
        $this->groups = $groups;

        return $this;
    }
}