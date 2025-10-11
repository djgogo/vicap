<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CkeditorUploadController extends AbstractController
{
    #[Route('/admin/ckeditor/upload', name: 'admin_ckeditor_upload', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(
        Request $request,
        EntityManagerInterface $em,
        CacheManager $cacheManager
    ): Response {
        // Optional CSRF (matches Twig header below)
        $csrf = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('ckeditor_upload', $csrf)) {
            return new JsonResponse(['error' => ['message' => 'Invalid CSRF token']], Response::HTTP_FORBIDDEN);
        }

        $file = $request->files->get('upload'); // CKEditor sends file under "upload"
        if (!$file || !$file->isValid()) {
            return new JsonResponse(['error' => ['message' => 'No/invalid file']], Response::HTTP_BAD_REQUEST);
        }

        // Basic validation
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return new JsonResponse(['error' => ['message' => 'Unsupported file type']], Response::HTTP_BAD_REQUEST);
        }

        // Optional size cap (e.g. 10 MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return new JsonResponse(['error' => ['message' => 'File too large']], Response::HTTP_BAD_REQUEST);
        }

        // Persist via VichUploader (sets filename and moves to mapping destination)
        $img = new Media();
        $fileOriginalName = $file->getClientOriginalName();
        $fileNameWithoutExt = pathinfo($fileOriginalName, PATHINFO_FILENAME);
        $img->setFile($file);
        $img->setFileName($fileNameWithoutExt);
        $img->setFileType($file->getMimeType());
        $img->setFileSize($file->getSize());
        $em->persist($img);
        $em->flush();

        // Public original path:
        $publicPath = 'uploads/' . $img->getFilePath();

        // Give CKEditor a LiipImagine-processed URL for the editor view:
        $previewUrl = $cacheManager->getBrowserPath($publicPath, 'editor_preview');

        // CKEditor 5 SimpleUpload expects { url: '...' }
        return new JsonResponse(['url' => $previewUrl], Response::HTTP_CREATED);
    }
}
