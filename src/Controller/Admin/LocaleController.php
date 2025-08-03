<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Controller used to manage the locale persistence in the database
 */
final class LocaleController extends AbstractController
{
    /**
     * Language locale code database persistence
     */
    #[Route("/change-locale", name: "language_locale_index", methods: ['POST'])]
    public function changeLocale(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // security check
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }

        // get the selected locale
        $data = json_decode($request->getContent(), true);
        $locale = $data['locale'] ?? null;

        if (!$locale) {
            return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        $user->setLocale($locale);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

}
