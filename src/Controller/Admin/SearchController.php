<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\PortfolioRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles brand entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/search')]
#[IsGranted(User::ROLE_ADMIN)]
class SearchController extends AbstractController
{
    private PortfolioRepository $portfolioRepository;

    public function __construct(
        PortfolioRepository $portfolioRepository,
    ) {
        $this->portfolioRepository = $portfolioRepository;
    }

    /**
     * Render a list of search results - AJAX search
     */
    #[Route('/', name: 'admin_search_index')]
    public function ajaxSearch(
        Request $request,
    ): JsonResponse {
        $term = trim($request->query->get('q', ''));
        $results = [];

        if ($term !== '') {
            // create the results
            $results = $this->createResults($term);
        }

        return $this->json($results);
    }

    /**
     * Render a list of search results - AJAX search
     */
    #[Route('/search-results/{term}', name: 'admin_search_result_page')]
    public function searchPage(
        #[CurrentUser] User $user,
        Request $request,
        PaginatorInterface $paginator,
        ?string $term = null,
    ): Response {
        if (!$term) {
            $this->addFlash('warning', 'flash_message.enter_search_term');
            return $this->redirectToRoute('admin_search_result_page');
        }

        // Create a flat array of results from all repositories
        $results = $this->createResults($term, false);

        // we use the paginator to paginate the flat results array.
        // The second parameter is the current page (defaulting to 1),
        // and the third parameter is the number of items per page.
        $pagination = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            10 // items per page
        );

        // Group the items from the current page by their "type"
        $groupedResults = [];
        foreach ($pagination->getItems() as $result) {
            $groupedResults[$result['type']][] = $result;
        }

        return $this->render('pages/search-results.html.twig', [
            'currentUser' => $user,
            'searchTerm' => $term,
            'pagination' => $pagination,
            'results' => $groupedResults,
        ]);
    }

    private function createResults(string $term, bool $limit = true): array
    {
        $results = [];

        // portfolios
        $portfolios = $this->portfolioRepository->getSearchResults($term, $limit);
        foreach ($portfolios as $portfolio) {
            $results[] = [
                'type'  => 'Portfolio',
                'label' => $portfolio->getName(),
                'url'   => $this->generateUrl('admin_portfolios_edit', ['id' => $portfolio->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        return $results;
    }

}