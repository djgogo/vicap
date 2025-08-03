<?php

namespace App\Controller\Admin;

use App\Entity\Trade;
use App\Entity\User;
use App\Form\TradeType;
use App\Repository\TradeRepository;
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
#[Route('/admin/trades')]
#[IsGranted(User::ROLE_ADMIN)]
class TradeController extends AbstractController
{
    private DateFormatService $dateFormatService;

    function __construct(DateFormatService $dateFormatService) {
        $this->dateFormatService = $dateFormatService;
    }

    /**
     * Render a list of trades.
     */
    #[Route('/', name: 'admin_trades_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        TradeRepository $tradeRepository,
        PaginatorInterface $paginator,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $tradeRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        // create the create new user form for the modal
        $form = $this->createForm(TradeType::class, new Trade());

        return $this->render('admin/trades/index.html.twig', [
            'trades' => $pagination,
            'currentUser' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Create Trade
     */
    #[Route('/create', name: 'admin_trades_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $trade = new Trade();
        $form = $this->createForm(TradeType::class, $trade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trade);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.trade_created');

            return $this->redirectToRoute('admin_trades_index');
        }

        return $this->redirectToRoute('admin_trades_index');
    }

    /**
     * render the trade edit page
     */
    #[Route('/edit/{id}', name: 'admin_trades_edit')]
    public function viewProfile(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response {
        $trade = $entityManager->getRepository(Trade::class)->findOneBy(['id' => $id]);
        if (!$trade) {
            throw $this->createNotFoundException('Trade not found');
        }

        $form = $this->createForm(TradeType::class, $trade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trade);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.trade_successfully_edited');

            return $this->redirectToRoute('admin_trades_edit', ['id' => $trade->getId()]);
        }

        return $this->render('admin/trades/trade-details.html.twig', [
            'trade' => $trade,
            'currentUser' => $currentUser,
            'form' => $form,
        ]);
    }

    /**
     * delete trade and all related entities
     */
    #[Route('/delete/{id}', name: 'admin_trades_delete', methods: ['POST'])]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response {
        if (!$id) {
            return $this->redirectToRoute('admin_trades_index');
        }

        $trade = $entityManager->getRepository(Trade::class)->findOneBy(['id' => $id]);
        if (!$trade) {
            throw $this->createNotFoundException('Trade not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($trade);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.trade_deleted');

            return $this->redirectToRoute('admin_trades_index');
        }

        return $this->redirectToRoute('admin_trades_index');
    }

    /**
     * export trades to CSV
     * @throws Exception
     */
    #[Route('/export.csv', name: 'admin_trades_csv_export', methods: ['GET'])]
    public function exportCoursesToCsv(
        TradeRepository $tradeRepository,
        Request $request,
    ): Response {
        // Fetch data
        $trades = $tradeRepository->findAll();

        // Create a League CSV Writer using an in-memory stream
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());

        // Insert header
        $csvWriter->insertOne([
            'ID',
            'CreatedAt',
            'UpdatedAt',
            'Category',
            'Lead',
            'Description',
            'Assigned Contacts',
            'References'
        ]);

        // Insert data rows
        foreach ($trades as $trade) {
            $csvWriter->insertOne([
                $trade->getId(),
                $trade->getCreated()->format($this->dateFormatService->getDateFormat($request)),
                $trade->getUpdated()->format($this->dateFormatService->getDateFormat($request)),
                $trade->getName(),
                $trade->getDescription(),
                implode(', ', $trade->getEmployees()),
                implode(', ', $trade->getReferences()),
            ]);
        }

        // Convert to string
        $csvContent = $csvWriter->toString();

        // Create response
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="trades.csv"');

        return $response;
    }


}