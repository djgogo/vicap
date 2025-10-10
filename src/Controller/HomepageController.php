<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Controller;

use App\Entity\BlogCategory;
use App\Entity\PortfolioCategory;
use App\Entity\Reference;
use App\Entity\TermTemplate;
use App\Entity\User;
use App\Repository\BlogRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ReferenceRepository;
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
        ReferenceRepository $referenceRepository,
        BlogRepository $blogRepository,
    ): Response {
        // get all dynamic stuff for the frontpage
        $blogs = $blogRepository->findAllOrderedDesc();
        $references = $referenceRepository->findAll();

        return $this->render('default/homepage.html.twig', [
            'blogs' => $blogs,
            'references' => $references,
        ]);
    }

    #[Route('/blog', name: 'homepage_blog', options: ['sitemap' => true])]
    public function viewBlog(
        EntityManagerInterface $entityManager,
        BlogRepository $blogRepository,
    ): Response {
        // No category filter – get all projects ordered DESC (latest first)
        $blogs = $blogRepository->findAllOrderedDesc();

        return $this->render('public/blog/blog.html.twig', [
            'blogs' => $blogs
        ]);
    }

    #[Route('/blog/{id}', name: 'homepage_blog_details')]
    public function viewBlogDetails(
        Request $request,
        EntityManagerInterface $entityManager,
        BlogRepository $blogRepository,
    ): Response {
        // Fetch project
        $blog = $blogRepository->find($request->get('id'));

        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        // Fetch previous and next blog news for pagination
        $previousBlog = $blogRepository->findPreviousBlog($blog->getId());
        $nextBlog = $blogRepository->findNextBlog($blog->getId());
        
        // fetch all related blog posts with the same category
        $relatedPosts = $blogRepository->findRelatedPostsByCategory($blog->getBlogCategories()->first());

        return $this->render('public/blog/blog-details.html.twig', [
            'blog' => $blog,
            'previousBlog' => $previousBlog,
            'nextBlog' => $nextBlog,
            'blogs' => $relatedPosts,
        ]);
    }

    #[Route('/portfolio', name: 'homepage_portfolio', options: ['sitemap' => true])]
    public function viewPortfolio(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
    ): Response {
        // Retrieve the selected category from the query string (if any)
        $categoryId = $request->query->getInt('category', 0);
        $selectedCategory = $entityManager->getRepository(PortfolioCategory::class)->find($categoryId);

        if ($categoryId) {
            // Filter employees by selected category
            $projects = $portfolioRepository->getProjectsByCategory($categoryId);
        } else {
            // No category filter – get all projects ordered DESC (latest first)
            $projects = $portfolioRepository->findAllOrderedDesc();
        }

        // Retrieve all categories for the filter bar
        $categories = $entityManager->getRepository(PortfolioCategory::class)->findAll();

        return $this->render('public/portfolio/portfolio.html.twig', [
            'projects' => $projects,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    #[Route('/portfolio/project/{id}', name: 'homepage_portfolio_details')]
    public function viewProjectDetails(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
    ): Response {
        // Fetch project
        $project = $portfolioRepository->find($request->get('id'));

        if (!$project) {
            throw $this->createNotFoundException('Portfolio project not found');
        }

        // Fetch previous and next portfolio projects for pagination
        $previousProject = $portfolioRepository->findPreviousPortfolio($project->getId());
        $nextProject = $portfolioRepository->findNextPortfolio($project->getId());

        return $this->render('public/portfolio/portfolio-details.html.twig', [
            'project' => $project,
            'previousProject' => $previousProject,
            'nextProject' => $nextProject,
        ]);
    }

    #[Route('/service', name: 'homepage_service', options: ['sitemap' => true])]
    public function viewServices(
    ): Response {
        return $this->render('public/service/service.html.twig', []);
    }

    #[Route('/service/website', name: 'homepage_service_website', options: ['sitemap' => true])]
    public function viewServiceWebsite(
    ): Response {
        return $this->render('public/service/service-website.html.twig', []);
    }

    #[Route('/service/app-development', name: 'homepage_service_app_development', options: ['sitemap' => true])]
    public function viewServiceAppDevelopment(
    ): Response {
        return $this->render('public/service/service-app-development.html.twig', []);
    }

    #[Route('/service/digital-commerce', name: 'homepage_service_digital_commerce', options: ['sitemap' => true])]
    public function viewServiceDigitalCommerce(
    ): Response {
        return $this->render('public/service/service-e-commerce.html.twig', []);
    }

    #[Route('/service/ai-powered-solutions', name: 'homepage_service_ai_powered_solutions', options: ['sitemap' => true])]
    public function viewServiceAiPoweredSolutions(
    ): Response {
        return $this->render('public/service/service-ai-powered-solutions.html.twig', []);
    }

    #[Route('/service/wordpress', name: 'homepage_service_wordpress', options: ['sitemap' => true])]
    public function viewServiceWordpress(
    ): Response {
        return $this->render('public/service/service-wordpress.html.twig', []);
    }

    #[Route('/service/web-hosting', name: 'homepage_service_web_hosting', options: ['sitemap' => true])]
    public function viewServiceWebHosting(
    ): Response {
        return $this->render('public/service/service-web-hosting.html.twig', []);
    }

    #[Route('/service/internet-radio', name: 'homepage_service_internet_radio', options: ['sitemap' => true])]
    public function viewServiceInternetRadio(
    ): Response {
        return $this->render('public/service/service-internet-radio.html.twig', []);
    }

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
