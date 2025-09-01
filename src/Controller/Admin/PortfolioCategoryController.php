<?php

namespace App\Controller\Admin;

use App\Entity\PortfolioCategory;
use App\Entity\User;
use App\Form\PortfolioCategoryType;
use App\Repository\PortfolioCategoryRepository;
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
 * Handles portfolio category entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/portfolios/categories')]
#[IsGranted(User::ROLE_ADMIN)]
class PortfolioCategoryController extends AbstractController
{
    /**
     * Render a list of portfolio categories
     */
    #[Route('/', name: 'admin_portfolios_categories_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        PortfolioCategoryRepository $portfolioCategoryRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $portfolioCategoryRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        $portfolioCategory = new PortfolioCategory();

        // create the create new portfolio-category form for the modal
        $form = $this->createForm(PortfolioCategoryType::class, $portfolioCategory);

        return $this->render('admin/portfolio/portfolio-categories.html.twig', [
            'portfolioCategory' => $portfolioCategory,
            'portfolio_categories' => $pagination,
            'currentUser' => $user,
            'form' => $form
        ]);
    }

    /**
     * Create Course Category
     */
    #[Route('/create', name: 'admin_portfolios_categories_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $portfolioCategory = new PortfolioCategory();
        $form = $this->createForm(PortfolioCategoryType::class, $portfolioCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($portfolioCategory);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.portfolio_category_created');
            return $this->redirectToRoute('admin_portfolios_categories_index');
        }

        return $this->redirectToRoute('admin_portfolios_categories_index');
    }

    /**
     * Edit Course Category - Ajax
     */
    #[Route('/edit/{id}', name: 'admin_portfolios_categories_edit', requirements: ['id' => '\d+'])]
    public function edit(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        ?int $id
    ): Response
    {
        $portfolioCategory = $entityManager->getRepository(PortfolioCategory::class)->find($id);
        if (!$portfolioCategory) {
            throw $this->createNotFoundException('Course Category not found!');
        }

        $form = $this->createForm(PortfolioCategoryType::class, $portfolioCategory, [
            'submit_label' => 'button.edit_portfolio_category',
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.portfolio_category_updated');
            return $this->redirectToRoute('admin_portfolios_categories_index');
        }

        return $this->render('admin/portfolio/portfolio-category-edit-modal.html.twig', [
            'id' => $id,
            'portfolioCategory' => $portfolioCategory,
            'currentUser' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete Course Category
     */
    #[Route('/delete/{id}', name: 'admin_portfolios_categories_delete')]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response
    {
        if (!$id) {
            return $this->redirectToRoute('admin_portfolios_categories_index');
        }

        $portfolioCategory = $entityManager->getRepository(PortfolioCategory::class)->find($id);
        if (!$portfolioCategory) {
            throw $this->createNotFoundException('Course Category not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($portfolioCategory);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.portfolio_category_deleted');
            return $this->redirectToRoute('admin_portfolios_categories_index');
        }

        return $this->redirectToRoute('admin_portfolios_categories_index');
    }

}