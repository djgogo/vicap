<?php

namespace App\Controller\Admin;

use App\Entity\Portfolio;
use App\Entity\User;
use App\Form\PortfolioType;
use App\Repository\PortfolioRepository;
use App\Service\DateFormatService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles user entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/portfolios')]
#[IsGranted(User::ROLE_ADMIN)]
class PortfolioController extends AbstractController
{
    private DateFormatService $dateFormatService;

    function __construct(DateFormatService $dateFormatService) {
        $this->dateFormatService = $dateFormatService;
    }

    /**
     * Render a list of portfolios.
     */
    #[Route('/', name: 'admin_portfolios_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        PortfolioRepository $portfolioRepository,
        PaginatorInterface $paginator,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $portfolioRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        // create the create new user form for the modal
        $form = $this->createForm(PortfolioType::class, new Portfolio());

        return $this->render('admin/portfolio/index.html.twig', [
            'portfolios' => $pagination,
            'currentUser' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Create Portfolio
     */
    #[Route('/create', name: 'admin_portfolios_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $portfolio = new Portfolio();
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($portfolio);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.portfolio_created');

            return $this->redirectToRoute('admin_portfolios_index');
        }

        return $this->redirectToRoute('admin_portfolios_index');
    }

    /**
     * render the portfolio edit page
     */
    #[Route('/edit/{id}', name: 'admin_portfolios_edit')]
    public function viewProfile(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response {
        $portfolio = $entityManager->getRepository(Portfolio::class)->findOneBy(['id' => $id]);
        if (!$portfolio) {
            throw $this->createNotFoundException('Portfolio not found');
        }

        $form = $this->createForm(PortfolioType::class, $portfolio, [
            'submit_label' => 'button.edit_portfolio',
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($portfolio);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.portfolio_successfully_edited');

            return $this->redirectToRoute('admin_portfolios_edit', ['id' => $portfolio->getId()]);
        }

        return $this->render('admin/portfolio/portfolio-details.html.twig', [
            'portfolio' => $portfolio,
            'currentUser' => $currentUser,
            'form' => $form,
        ]);
    }

    /**
     * delete portfolio and all related entities
     */
    #[Route('/delete/{id}', name: 'admin_portfolios_delete', methods: ['POST'])]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response {
        if (!$id) {
            return $this->redirectToRoute('admin_portfolios_index');
        }

        $portfolio = $entityManager->getRepository(Portfolio::class)->findOneBy(['id' => $id]);
        if (!$portfolio) {
            throw $this->createNotFoundException('Portfolio not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($portfolio);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.portfolio_deleted');

            return $this->redirectToRoute('admin_portfolios_index');
        }

        return $this->redirectToRoute('admin_portfolios_index');
    }

    /**
     * export portfolios to CSV
     * @throws Exception
     */
    #[Route('/export.csv', name: 'admin_portfolios_csv_export', methods: ['GET'])]
    public function exportCoursesToCsv(
        PortfolioRepository $portfolioRepository,
        Request $request,
    ): Response {
        // Fetch data
        $portfolios = $portfolioRepository->findAll();

        // Create a League CSV Writer using an in-memory stream
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());

        // Insert header
        $csvWriter->insertOne([
            'ID',
            'CreatedAt',
            'UpdatedAt',
            'Name',
            'Client',
            'Category',
            'Features',
            'Technologies',
            'Description'
        ]);

        // Insert data rows
        foreach ($portfolios as $portfolio) {
            $csvWriter->insertOne([
                $portfolio->getId(),
                $portfolio->getCreated()->format($this->dateFormatService->getDateFormat($request)),
                $portfolio->getUpdated()->format($this->dateFormatService->getDateFormat($request)),
                $portfolio->getName(),
                $portfolio->getClient(),
                $portfolio->getPortfolioCategory()->getId(),
                $portfolio->getFeatures(),
                $portfolio->getTechnologies(),
                $portfolio->getDescription()
            ]);
        }

        // Convert to string
        $csvContent = $csvWriter->toString();

        // Create response
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="portfolio.csv"');

        return $response;
    }


}