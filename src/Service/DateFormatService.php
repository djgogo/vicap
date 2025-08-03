<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides methods to retrieve date and time formats based on the locale.
 */
class DateFormatService
{
    /**
     * @return string[]
     */
    public function getDateFormats(Request $request): array
    {
        $locale = $request->getLocale();

        $date_format = match ($locale) {
            'en' => 'Y-m-d H:i',
            'de' => 'd.m.Y H:i',
            'it' => 'd/m/Y H:i',
            'fr' => 'd/m/Y H:i',
            'es' => 'd/m/Y H:i',
            default => 'Y-m-d H:i',
        };

        $format = match ($locale) {
            'en' => 'yyyy-MM-dd HH:mm',
            'de' => 'dd.MM.yyyy HH:mm',
            'it' => 'dd/MM/yyyy HH:mm',
            'fr' => 'dd/MM/yyyy HH:mm',
            'es' => 'dd/MM/yyyy HH:mm',
            default => 'yyyy-MM-dd HH:mm',
        };

        return [
            'format' => $format,
            'date_format' => $date_format
        ];
    }

    public function getDateFormat(Request $request): string
    {
        $locale = $request->getLocale();

        return match ($locale) {
            'en' => 'Y-m-d H:i',
            'de' => 'd.m.Y H:i',
            'it' => 'd/m/Y H:i',
            'fr' => 'd/m/Y H:i',
            'es' => 'd/m/Y H:i',
            default => 'Y-m-d H:i',
        };
    }
}
