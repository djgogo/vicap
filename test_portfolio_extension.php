<?php

require_once 'vendor/autoload.php';

use App\Twig\PortfolioExtension;

echo "Testing PortfolioExtension...\n";

try {
    // Test that the PortfolioExtension class exists
    if (class_exists('App\Twig\PortfolioExtension')) {
        echo "✓ PortfolioExtension class exists\n";

        // Test that the portfolioImage method exists
        if (method_exists(PortfolioExtension::class, 'portfolioImage')) {
            echo "✓ portfolioImage method exists\n";

            // Test that getFunctions returns the portfolio_image function
            $reflection = new ReflectionClass(PortfolioExtension::class);
            $getFunctionsMethod = $reflection->getMethod('getFunctions');

            // We can't easily instantiate the extension without its dependencies,
            // but we can check the source code to see if it registers the function correctly
            $source = file_get_contents('src/Twig/PortfolioExtension.php');

            if (strpos($source, "new TwigFunction('portfolio_image', [\$this, 'portfolioImage'])") !== false) {
                echo "✓ portfolio_image function is properly registered to portfolioImage method\n";
                echo "✓ The method name mismatch has been fixed!\n";
            } else {
                echo "✗ portfolio_image function registration not found or incorrect\n";
            }

        } else {
            echo "✗ portfolioImage method does not exist\n";
        }
    } else {
        echo "✗ PortfolioExtension class does not exist\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
