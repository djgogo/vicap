<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Controller;

use App\Entity\TermTemplate;
use App\Entity\TradeCategory;
use App\Entity\User;
use App\Repository\TradeRepository;
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

    #[Route('/people', name: 'homepage_people', options: ['sitemap' => true])]
    public function viewPeople(
        Request $request,
        EntityManagerInterface $entityManager,
        TradeRepository $tradeRepository,
    ): Response {
        // Retrieve the selected category from the query string (if any)
        $categoryId = $request->query->getInt('category', 0);
        $selectedCategory = $entityManager->getRepository(TradeCategory::class)->find($categoryId);

        if ($categoryId) {
            // Filter employees by selected category
            $trades = $tradeRepository->getTradesByCategory($categoryId);
        } else {
            // No category filter – get all employees
            $trades = $tradeRepository->findAll();
        }

        // Retrieve all categories for the filter bar
        $categories = $entityManager->getRepository(TradeCategory::class)->findAll();

        return $this->render('public/trades/people.html.twig', [
            'trades' => $trades,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    #[Route('/people/trade/{id}', name: 'homepage_people_details')]
    public function viewTradeDetails(
        Request $request,
        EntityManagerInterface $entityManager,
        TradeRepository $tradeRepository,
    ): Response {
        // Fetch trade
        $trade = $tradeRepository->find($request->get('id'));

        return $this->render('public/trades/trade-details.html.twig', [
            'trade' => $trade,
        ]);
    }


    #[Route('/contact', name: 'homepage_contact', options: ['sitemap' => true])]
    public function contact(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        return $this->render('public/contact.html.twig', []);
    }

    #[Route('/impressum', name: 'homepage_impressum', options: ['sitemap' => true])]
    public function viewImpressum(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        // Find the TermTemplate for the impressum and locale
        $impressum = $entityManager->getRepository(TermTemplate::class)->findOneBy(['name' => 'website_impressum', 'locale' => 'de']);

        return $this->render('public/impressum.html.twig', [
            'impressum' => $impressum,
        ]);
    }

    #[Route('/certifiates', name: 'homepage_certifiates', options: ['sitemap' => true])]
    public function viewCertifiates(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        // Find the TermTemplate for the certificates and locale
        $certificates = $entityManager->getRepository(TermTemplate::class)->findOneBy(['name' => 'website_certificates', 'locale' => 'de']);

        return $this->render('public/certificates.html.twig', [
            'certificates' => $certificates,
        ]);
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