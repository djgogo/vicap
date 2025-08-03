<?php

namespace App\Controller\Admin;

use App\Entity\TradeCategory;
use App\Entity\User;
use App\Form\TradeCategoryType;
use App\Repository\TradeCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles trade category entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/trades/categories')]
#[IsGranted(User::ROLE_ADMIN)]
class TradeCategoryController extends AbstractController
{
    /**
     * Render a list of trade categories
     */
    #[Route('/', name: 'admin_trades_categories_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        TradeCategoryRepository $tradeCategoryRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $tradeCategoryRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        $tradeCategory = new TradeCategory();

        // create the create new trade-category form for the modal
        $form = $this->createForm(TradeCategoryType::class, $tradeCategory);

        return $this->render('admin/trades/trade-categories.html.twig', [
            'tradeCategory' => $tradeCategory,
            'trade_categories' => $pagination,
            'currentUser' => $user,
            'form' => $form
        ]);
    }

    /**
     * Create Course Category
     */
    #[Route('/create', name: 'admin_trades_categories_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $tradeCategory = new TradeCategory();
        $form = $this->createForm(TradeCategoryType::class, $tradeCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tradeCategory);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.trade_category_created');
            return $this->redirectToRoute('admin_trades_categories_index');
        }

        return $this->redirectToRoute('admin_trades_categories_index');
    }

    /**
     * Edit Course Category - Ajax
     */
    #[Route('/edit/{id}', name: 'admin_trades_categories_edit', requirements: ['id' => '\d+'])]
    public function edit(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        ?int $id
    ): Response
    {
        $tradeCategory = $entityManager->getRepository(TradeCategory::class)->find($id);
        if (!$tradeCategory) {
            throw $this->createNotFoundException('Course Category not found!');
        }

        $form = $this->createForm(TradeCategoryType::class, $tradeCategory, [
            'submit_label' => 'button.edit_trade_category',
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.trade_category_updated');
            return $this->redirectToRoute('admin_trades_categories_index');
        }

        return $this->render('admin/trades/trade-category-edit-modal.html.twig', [
            'id' => $id,
            'tradeCategory' => $tradeCategory,
            'currentUser' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete Course Category
     */
    #[Route('/delete/{id}', name: 'admin_trades_categories_delete')]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response
    {
        if (!$id) {
            return $this->redirectToRoute('admin_trades_categories_index');
        }

        $tradeCategory = $entityManager->getRepository(TradeCategory::class)->find($id);
        if (!$tradeCategory) {
            throw $this->createNotFoundException('Course Category not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($tradeCategory);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.trade_category_deleted');
            return $this->redirectToRoute('admin_trades_categories_index');
        }

        return $this->redirectToRoute('admin_trades_categories_index');
    }

}