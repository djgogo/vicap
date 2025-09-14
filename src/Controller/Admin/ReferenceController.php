<?php

namespace App\Controller\Admin;

use App\Entity\Reference;
use App\Entity\User;
use App\Form\ReferenceType;
use App\Repository\ReferenceRepository;
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
 * Handles client testimonial references brands entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/references')]
#[IsGranted(User::ROLE_ADMIN)]
class ReferenceController extends AbstractController
{
    /**
     * Render a list of references / brands
     */
    #[Route('/', name: 'admin_references_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        ReferenceRepository $referenceRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $referenceRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        $reference = new Reference();

        // create the create new references form for the modal
        $form = $this->createForm(ReferenceType::class, $reference);

        return $this->render('admin/clients/references/references.html.twig', [
            'reference' => $reference,
            'references' => $pagination,
            'currentUser' => $user,
            'form' => $form
        ]);
    }

    /**
     * Create Course Category
     */
    #[Route('/create', name: 'admin_references_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $reference = new Reference();
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reference);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.references_created');
            return $this->redirectToRoute('admin_references_index');
        }

        return $this->redirectToRoute('admin_references_index');
    }

    /**
     * Edit Course Category - Ajax
     */
    #[Route('/edit/{id}', name: 'admin_references_edit', requirements: ['id' => '\d+'])]
    public function edit(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        ?int $id
    ): Response
    {
        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            throw $this->createNotFoundException('Reference not found!');
        }

        $form = $this->createForm(ReferenceType::class, $reference, [
            'submit_label' => 'button.edit_references',
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.references_updated');
            return $this->redirectToRoute('admin_references_index');
        }

        return $this->render('admin/clients/references/references-edit-modal.html.twig', [
            'id' => $id,
            'reference' => $reference,
            'currentUser' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete Course Category
     */
    #[Route('/delete/{id}', name: 'admin_references_delete')]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): RedirectResponse|Response
    {
        if (!$id) {
            return $this->redirectToRoute('admin_references_index');
        }

        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            throw $this->createNotFoundException('Course Category not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($reference);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.references_deleted');
            return $this->redirectToRoute('admin_references_index');
        }

        return $this->redirectToRoute('admin_references_index');
    }

}