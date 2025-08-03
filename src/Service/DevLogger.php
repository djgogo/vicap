<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class DevLogger
{
    private $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    public function log($variable, string $text = '', string $line = ''): void
    {
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('Europe/Zurich'));
        $dirpath = $this->projectDir . '/var/log';
        $path = $this->projectDir . '/var/log/debug.log';

        if (!file_exists($dirpath)) {
            mkdir($dirpath, 0755, true);
        }

        if (is_array($variable) || is_object($variable)) {
            $array = print_r($variable, true);
            $logEntry = $dateTime->format('Y/m/d H:i:s') . ' / ' .
                $line . ' / ' .
                $text . ' / ' .
                $array;
        } elseif (is_bool($variable)) {
            $variable = $variable ? 'true' : 'false';
            $logEntry = $dateTime->format('Y/m/d H:i:s') . ' / ' .
                $line . ' / ' .
                $text . ' / ' .
                $variable;
        } else {
            $logEntry = $dateTime->format('Y/m/d H:i:s') . ' / ' .
                $line . ' / ' .
                $text . ' / ' .
                $variable;
        }

        /**
         * message-type 3: message wird an die Datei (destination) angefügt.
         * Ein Zeilenumbruch wird nicht automatisch an das Ende des message-Strings angehängt.
         */
        error_log($logEntry . PHP_EOL, 3, $path);
    }
}