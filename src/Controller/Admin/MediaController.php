<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\MediaRepository;
use App\Service\MediaFileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles files entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/media')]
#[IsGranted(User::ROLE_ADMIN)]
class MediaController extends AbstractController
{
    private MediaFileService $fileService;

    public function __construct(MediaFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Render a list of document files
     */
    #[Route('/', name: 'admin_media_files_index')]
    public function index(
        #[CurrentUser] User $user,
        MediaRepository $mediaRepository,
    ): Response
    {
        $files = $mediaRepository->findAll();

        return $this->render('admin/media/index.html.twig', [
            'currentUser' => $user,
            'files' => $files
        ]);
    }

    /**
     * Delete a media file
     */
    #[Route('/delete/{id}', name: 'media_files_delete', methods: ['POST'])]
    public function deleteMediaFile(
        #[CurrentUser] User $user,
        Request $request,
        int $id
    ): JsonResponse {
        // Delete the file using the service
        $result = $this->fileService->deleteFile($id, $user, $request->getContent(), '/uploads');

        if ($result['success']) {
            return $this->json(['success' => true, 'message' => 'File deleted successfully.'], JsonResponse::HTTP_OK);
        } else {
            return $this->json(['success' => false, 'error' => $result['error']], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

}