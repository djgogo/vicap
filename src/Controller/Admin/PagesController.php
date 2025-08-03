<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
#[Route('/admin/pages')]
final class PagesController extends AbstractController
{
    /**
     * The FAQ page
     */
    #[Route('/faq', name: 'pages_faqs')]
    public function showFaqPage(
        #[CurrentUser] ?User $user,
        Request $request,
    ): Response {
        return $this->render('admin/pages/faqs.html.twig', [
            'currentUser' => $user
        ]);
    }

    /**
     * The Search Results page
     */
    #[Route('/search-results', name: 'search_results')]
    public function showSearchResults(
        #[CurrentUser] ?User $user,
        Request $request,
    ): Response {
        return $this->render('pages/search-results.html.twig');
        // TODO
    }

}
