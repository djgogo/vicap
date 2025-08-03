<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles user entity manipulations.
 *
 * @package App\Controller
 */
#[Route('/admin/users/options')]
#[IsGranted(User::ROLE_ADMIN)]
class UserOptionsController extends AbstractController
{
    /**
     * AJAX edit option switchers.
     */
    #[Route('/edit/{id}/{option}', name: 'admin_users_options_edit', defaults: ['id' => null], methods: ['POST'])]
    public function index(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id,
        string $option,
    ): Response {
        // Parse incoming JSON body
        $data = json_decode($request->getContent(), true);
        $newValue = isset($data['value']) ? (int) $data['value'] : 0;

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            return $this->json(['errors' => true, 'message' => 'User not found.'], 400);
        }

        // Find or create the UserOption entry
        $userOption = $entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $user,
            'name' => $option
        ]);

        if (!$userOption) {
            $userOption = new UserOption();
            $userOption->setUser($user);
            $userOption->setName($option);
        }
        $userOption->setValue($newValue);

        $entityManager->persist($userOption);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Option edited successfully.'], 200);
    }

}