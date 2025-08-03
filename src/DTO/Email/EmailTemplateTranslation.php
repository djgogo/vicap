<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\DTO\Email;

class EmailTemplateTranslation
{
    public string $locale;
    public string $subject;
    public string $content;

    public function __construct(string $locale = '', string $subject = '', string $content = '')
    {
        $this->locale = $locale;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}