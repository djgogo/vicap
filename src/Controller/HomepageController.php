<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Controller;

use App\Entity\TermTemplate;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * This is the Frontpage Controller
 *
 * for performance reasons we create the Sitemap xml files statically.
 * To renew the sitemap call: php bin/console presta:sitemap:dump
 */
#[Route('/')]
class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage_index', options: ['sitemap' => true])]
    public function index(
    ): Response {
        return $this->render('default/homepage.html.twig', []);
    }
//
//    #[Route('/portfolio', name: 'homepage_portfolio', options: ['sitemap' => true])]
//    public function viewPortfolio(
//        Request $request,
//        EntityManagerInterface $entityManager,
//        ProjectRepository $projectRepository,
//    ): Response {
//        // Retrieve the selected category from the query string (if any)
//        $categoryId = $request->query->getInt('category', 0);
//        $selectedCategory = $entityManager->getRepository(ProjectCategory::class)->find($categoryId);
//
//        if ($categoryId) {
//            // Filter employees by selected category
//            $rojects = $projectRepository->getTradesByCategory($categoryId);
//        } else {
//            // No category filter – get all employees
//            $rojects = $projectRepository->findAll();
//        }
//
//        // Retrieve all categories for the filter bar
//        $categories = $entityManager->getRepository(ProjectCategory::class)->findAll();
//
//        return $this->render('public/portfolio/portfolio.html.twig', [
//            'projects' => $rojects,
//            'categories' => $categories,
//            'selectedCategoryId' => $categoryId,
//            'selectedCategory' => $selectedCategory,
//        ]);
//    }
//
//    #[Route('/portfolio/project/{id}', name: 'homepage_portfolio_details')]
//    public function viewProjectDetails(
//        Request $request,
//        EntityManagerInterface $entityManager,
//        ProjectRepository $projectRepository,
//    ): Response {
//        // Fetch trade
//        $project = $projectRepository->find($request->get('id'));
//
//        return $this->render('public/portfolio/portfolio-details.html.twig', [
//            'project' => $project,
//        ]);
//    }

    #[Route('/contact', name: 'homepage_contact', options: ['sitemap' => true])]
    public function contact(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        return $this->render('public/contact.html.twig', []);
    }

    #[Route('/privacy-policy', name: 'homepage_privacy_policy', options: ['sitemap' => true])]
    public function viewPrivacyPolicy(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        // Find the TermTemplate for the privacy-policy and locale
        $privacyPolicy = $entityManager->getRepository(TermTemplate::class)->findOneBy(['name' => 'website_privacy_policy', 'locale' => 'de']);

        return $this->render('public/privacy-policy.html.twig', [
            'privacyPolicy' => $privacyPolicy,
        ]);
    }

    #[Route('/footer', name: 'footer')]
    public function footer(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        return $this->render('public/partials/footer.html.twig', []);
    }
}