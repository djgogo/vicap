<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class TrackingExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private array $pixels;

    public function __construct(RequestStack $requestStack, array $pixels)
    {
        $this->requestStack = $requestStack;
        $this->pixels = $pixels;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('append_utm', [$this, 'appendUtm']),
            new TwigFunction('tracking_pixel', [$this, 'renderPixel'], ['is_safe' => ['html']]),
        ];
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function appendUtm(string $url): string
    {
        $session = $this->getSession();
        $parts   = parse_url($url);
        parse_str($parts['query'] ?? '', $qs);

        foreach (['utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $k) {
            if ($v = $session->get($k)) {
                $qs[$k] = $v;
            }
        }

        $parts['query'] = http_build_query($qs);
        return (isset($parts['scheme']) ? "{$parts['scheme']}://" : '')
            . ($parts['host'] ?? '')
            . ($parts['path'] ?? '')
            . (!empty($parts['query']) ? "?{$parts['query']}" : '');
    }

    public function renderPixel(string $provider, string $action): string
    {
        if (isset($this->pixels[$provider]) && isset($this->pixels[$provider][$action])) {
            $src = $this->pixels[$provider][$action];
            return sprintf("<img height=\"1\" width=\"1\" border=\"0\" src=\"%s\" />", htmlspecialchars($src));
        }
        return '';
    }
}
