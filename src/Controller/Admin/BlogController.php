<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use App\Entity\User;
use App\Form\BlogType;
use App\Repository\BlogRepository;
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
#[Route('/admin/blogs')]
#[IsGranted(User::ROLE_ADMIN)]
class BlogController extends AbstractController
{
    private DateFormatService $dateFormatService;

    function __construct(DateFormatService $dateFormatService) {
        $this->dateFormatService = $dateFormatService;
    }

    /**
     * Render a list of blogs.
     */
    #[Route('/', name: 'admin_blogs_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        BlogRepository $blogRepository,
        PaginatorInterface $paginator,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $blogRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 12);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        // create the create new blog form for the modal and prefill author with current user
        $prefilledBlog = (new Blog())->setAuthor($user);
        $form = $this->createForm(BlogType::class, $prefilledBlog, [
            'current_user' => $user,
        ]);

        return $this->render('admin/blog/index.html.twig', [
            'blogs' => $pagination,
            'currentUser' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Create Blog
     */
    #[Route('/create', name: 'admin_blogs_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $currentUser,
    ): RedirectResponse {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog, [
            'current_user' => $currentUser,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Ensure author is set before validation (Doctrine adds NotNull via metadata)
            if (empty($blog->getAuthor())) {
                $blog->setAuthor($currentUser);
            }

            if ($form->isValid()) {
                $entityManager->persist($blog);
                $entityManager->flush();

                $this->addFlash('success', 'flash_message.blog_created');

                return $this->redirectToRoute('admin_blogs_index');
            }
        }

        return $this->redirectToRoute('admin_blogs_index');
    }

    /**
     * render the blog edit page
     */
    #[Route('/edit/{id}', name: 'admin_blogs_edit')]
    public function viewProfile(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response {
        $blog = $entityManager->getRepository(Blog::class)->findOneBy(['id' => $id]);
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $form = $this->createForm(BlogType::class, $blog, [
            'submit_label' => 'button.edit_blog',
            'current_user' => $currentUser,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.blog_successfully_edited');

            return $this->redirectToRoute('admin_blogs_edit', ['id' => $blog->getId()]);
        }

        return $this->render('admin/blog/blog-details.html.twig', [
            'blog' => $blog,
            'currentUser' => $currentUser,
            'form' => $form,
        ]);
    }

    /**
     * delete blog and all related entities
     */
    #[Route('/delete/{id}', name: 'admin_blogs_delete', methods: ['POST'])]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response {
        if (!$id) {
            return $this->redirectToRoute('admin_blogs_index');
        }

        $blog = $entityManager->getRepository(Blog::class)->findOneBy(['id' => $id]);
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($blog);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.blog_deleted');

            return $this->redirectToRoute('admin_blogs_index');
        }

        return $this->redirectToRoute('admin_blogs_index');
    }

    /**
     * export blogs to CSV
     * @throws Exception
     */
    #[Route('/export.csv', name: 'admin_blogs_csv_export', methods: ['GET'])]
    public function exportCoursesToCsv(
        BlogRepository $blogRepository,
        Request $request,
    ): Response {
        // Fetch data
        $blogs = $blogRepository->findAll();

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
        foreach ($blogs as $blog) {
            $csvWriter->insertOne([
                $blog->getId(),
                $blog->getCreated()->format($this->dateFormatService->getDateFormat($request)),
                $blog->getUpdated()->format($this->dateFormatService->getDateFormat($request)),
                $blog->getName(),
                $blog->getClient(),
                $blog->getBlogCategory()->getId(),
                $blog->getFeatures(),
                $blog->getTechnologies(),
                $blog->getDescription()
            ]);
        }

        // Convert to string
        $csvContent = $csvWriter->toString();

        // Create response
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="blog.csv"');

        return $response;
    }


}