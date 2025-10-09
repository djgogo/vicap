<?php

namespace App\Controller\Admin;

use App\Entity\BlogCategory;
use App\Entity\User;
use App\Form\BlogCategoryType;
use App\Repository\BlogCategoryRepository;
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
 * Handles blog category entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/blogs/categories')]
#[IsGranted(User::ROLE_ADMIN)]
class BlogCategoryController extends AbstractController
{
    /**
     * Render a list of blog categories
     */
    #[Route('/', name: 'admin_blogs_categories_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        BlogCategoryRepository $blogCategoryRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $blogCategoryRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        $blogCategory = new BlogCategory();

        // create the create new blog-category form for the modal
        $form = $this->createForm(BlogCategoryType::class, $blogCategory);

        return $this->render('admin/blog/blog-categories.html.twig', [
            'blogCategory' => $blogCategory,
            'blog_categories' => $pagination,
            'currentUser' => $user,
            'form' => $form
        ]);
    }

    /**
     * Create Course Category
     */
    #[Route('/create', name: 'admin_blogs_categories_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $blogCategory = new BlogCategory();
        $form = $this->createForm(BlogCategoryType::class, $blogCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blogCategory);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.blog_category_created');
            return $this->redirectToRoute('admin_blogs_categories_index');
        }

        return $this->redirectToRoute('admin_blogs_categories_index');
    }

    /**
     * Edit Course Category - Ajax
     */
    #[Route('/edit/{id}', name: 'admin_blogs_categories_edit', requirements: ['id' => '\d+'])]
    public function edit(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        ?int $id
    ): Response
    {
        $blogCategory = $entityManager->getRepository(BlogCategory::class)->find($id);
        if (!$blogCategory) {
            throw $this->createNotFoundException('Course Category not found!');
        }

        $form = $this->createForm(BlogCategoryType::class, $blogCategory, [
            'submit_label' => 'button.edit_blog_category',
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.blog_category_updated');
            return $this->redirectToRoute('admin_blogs_categories_index');
        }

        return $this->render('admin/blog/blog-category-edit-modal.html.twig', [
            'id' => $id,
            'blogCategory' => $blogCategory,
            'currentUser' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete Course Category
     */
    #[Route('/delete/{id}', name: 'admin_blogs_categories_delete')]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response
    {
        if (!$id) {
            return $this->redirectToRoute('admin_blogs_categories_index');
        }

        $blogCategory = $entityManager->getRepository(BlogCategory::class)->find($id);
        if (!$blogCategory) {
            throw $this->createNotFoundException('Course Category not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($blogCategory);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.blog_category_deleted');
            return $this->redirectToRoute('admin_blogs_categories_index');
        }

        return $this->redirectToRoute('admin_blogs_categories_index');
    }

}