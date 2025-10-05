<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\Controller\Upload;

use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles user entity manipulations.
 *
 * @package App\Controller
 */
//#[Route('/upload')]
class FileUploadController extends AbstractController
{
    /**
     * AJAX profile image upload
     */
//    #[Route('/profile-image/{id}', name: 'user_profile_image_upload', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadProfileImage(
        Security $security,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $uploader,
        int $id = null
    ): RedirectResponse {

        // security checks
        if (!$security->isGranted('ROLE_ADMIN') && !$security->isGranted('ROLE_USER')) {
            throw $this->createNotFoundException('You do not have the necessary permissions.');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $file = $request->files->get('profileImage');

        if ($file && $id) {

            // Validate the file
            $errors = $uploader->validate($file);

            if (count($errors) > 0) {
                // If there are errors, set them as flash messages
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }

                // Redirect back to the form or the appropriate page
                return $this->redirectToRoute('admin_user_edit', ['id' => $id, 'activeTab' => 'personalDetails']);
            }

            // Process the file upload
            $profileImageFileName = $uploader->upload($file, 'avatars');
            $user->setPhoto($profileImageFileName);
            $entityManager->flush();

            $this->addFlash('success', 'flash_message.profile_image_uploaded');
            return $this->redirectToRoute('admin_user_edit', ['id' => $id, 'activeTab' => 'personalDetails']);
        }

        $this->addFlash('success', 'flash_message.profile_image_upload_failed');
        return $this->redirectToRoute('admin_user_edit', ['id' => $id, 'activeTab' => 'personalDetails']);
    }

}