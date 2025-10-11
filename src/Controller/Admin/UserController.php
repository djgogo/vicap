<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserOption;
use App\Form\CreateUserForm;
use App\Form\UserProfile\ChangePasswordType;
use App\Form\UserProfile\PersonalDetailsType;
use App\Repository\UserRepository;
use App\Security\AuthManager;
use App\Service\AvatarFileService;
use App\Service\DateFormatService;
use App\Service\GravatarSupplier;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
#[Route('/admin/users')]
#[IsGranted(User::ROLE_ADMIN)]
class UserController extends AbstractController
{
    private DateFormatService $dateFormatService;
    private EntityManagerInterface $entityManager;

    function __construct(DateFormatService $dateFormatService, EntityManagerInterface $entityManager) {
        $this->dateFormatService = $dateFormatService;
        $this->entityManager = $entityManager;
    }

    /**
     * Render a list of users.
     */
    #[Route('/', name: 'admin_users_index')]
    public function index(
        #[CurrentUser] User $user,
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        AuthManager $authManager
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('query', null);
        $query = $userRepository->getListQuery($search);
        $pagination = $paginator->paginate($query, $page, 20);
        $pagination->setCustomParameters([
            'align' => 'right',
        ]);

        // create the create new user form for the modal
        $form = $this->createForm(CreateUserForm::class, new User());

        return $this->render('admin/users/index.html.twig', [
            'items' => $pagination,
            'currentUser' => $user,
            'form' => $form
        ]);
    }

    /**
     * Create User
     */
    #[Route('/create', name: 'admin_users_create')]
    public function create(
        Request $request,
        AuthManager $authManager,
        GravatarSupplier $gravatarSupplier,
    ): RedirectResponse
    {
        $user = new User();
        $form = $this->createForm(CreateUserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Get the user entity from the form
            /** @var User $user */
            $user = $form->getData();

            // set the user roles and password and save the user
            $role = $form->get('roles')->getData();
            $user->setRoles([$role]);
            $user->setIsEmailConfirmed(true);
            $gravatarSupplier->setGravatar($user);
            $authManager->register($user);

            $defaultOptions = [
                ['name' => 'notifications_enabled', 'value' => 1],
                ['name' => 'email_notifications_enabled', 'value' => 1],
            ];

            foreach ($defaultOptions as $opt) {
                $userOption = new UserOption();
                $userOption->setUser($user);
                $userOption->setName($opt['name']);
                $userOption->setValue($opt['value']);

                $this->entityManager->persist($userOption);
            }

            $this->addFlash('success', 'flash_message.user_created');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->redirectToRoute('admin_users_index');
    }

    /**
     * Render user edit forms.
     */
    #[Route('/edit/{id}', name: 'admin_user_edit')]
    public function edit(
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $personalDetailsForm = $this->createForm(PersonalDetailsType::class, $user);
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);

        // get the actual options values
        $options = $entityManager->getRepository(UserOption::class)->findBy(['user' => $user]);
        $mappedOptions = [];
        foreach ($options as $opt) {
            $mappedOptions[$opt->getName()] = $opt->isValue();
        }

        return $this->render('admin/users/user-profile.html.twig', [
            'user' => $user,
            'currentUser' => $currentUser,
            'personalDetails' => $personalDetailsForm->createView(),
            'changePassword' => $changePasswordForm->createView(),
            'options' => $mappedOptions,
        ]);
    }

    /**
     * Handle personalDetails tab form Request
     */
    #[Route('/personal-details/{id}', name: 'admin_user_update_personal_details')]
    public function editPersonalDetails(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(PersonalDetailsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.personal_details_updated');

            return $this->redirectToRoute('admin_user_edit', ['id' => $id,]);
        } else {
            $this->addFlash('error', 'Failed to update personal details.');
        }

        return $this->redirectToRoute('admin_user_edit', ['id' => $id,]);
    }

    /**
     * Handle changePassword tab form Request
     */
    #[Route('/change-password/{id}', name: 'admin_user_change_password')]
    public function changePassword(
        Request $request,
        AuthManager $authManager,
        EntityManagerInterface $entityManager,
        int $id = null
    ): RedirectResponse|Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $authManager->changePassword($user, $plainPassword);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.password_updated');

            return $this->redirectToRoute('admin_user_edit', ['id' => $id,]);
        } else {
            $this->addFlash('error', 'Failed to update password.');
        }

        return $this->redirectToRoute('admin_user_edit', ['id' => $id,]);
    }

    /**
     * AJAX edit option switchers.
     */
    #[Route('/options/edit/{option}', name: 'admin_user_profile_options_edit', methods: ['POST'])]
    public function editUserOption(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        string $option,
    ): Response {
        // Parse incoming JSON body
        $data = json_decode($request->getContent(), true);
        $newValue = isset($data['value']) ? (int) $data['value'] : 0;

        // Fetch the *current* values of both notification settings
        $notificationsEnabledOption = $entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $currentUser,
            'name' => 'notifications_enabled'
        ]);
        $emailNotificationsOption = $entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $currentUser,
            'name' => 'email_notifications_enabled'
        ]);

        // Fallback to 1 if not set or create them if not found
        $notificationsEnabledValue = $notificationsEnabledOption ? (int) $notificationsEnabledOption->isValue() : 1;
        $emailNotificationsValue = $emailNotificationsOption ? (int) $emailNotificationsOption->isValue() : 1;

        // Step 2: If the user is toggling one, figure out the *intended* final state
        if ($option === 'notifications_enabled') {
            $intendedNotificationsValue = $newValue;
            $intendedEmailValue = $emailNotificationsValue;
        } else {
            $intendedNotificationsValue = $notificationsEnabledValue;
            $intendedEmailValue = $newValue;
        }

        // Step 3: Check if the user is trying to disable both
        if ($intendedNotificationsValue === 0 && $intendedEmailValue === 0) {
            return $this->json([
                'success' => false,
                'errors' => ['You cannot disable both notification methods.']
            ], 400);
        }

        // Find or create the UserOption entry
        $userOption = $entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $currentUser,
            'name' => $option
        ]);

        if (!$userOption) {
            $userOption = new UserOption();
            $userOption->setUser($currentUser);
            $userOption->setName($option);
        }
        $userOption->setValue($newValue);

        $entityManager->persist($userOption);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Option edited successfully.'], 200);
    }

    /**
     * delete user and all related entities
     */
    #[Route('/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        AvatarFileService $avatarFileService,
        int $id
    ): RedirectResponse|Response {
        if (!$id) {
            return $this->redirectToRoute('admin_users_index');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if ($currentUser->getId() === $id) {
            $this->addFlash('warning', 'error.cannot_delete_yourself');
            return $this->redirectToRoute('admin_users_index');
        }

        if ($request->isMethod('POST')) {
            // delete avatar
            $avatarFileService->deleteAvatar($user->getPhoto(), '/users');

            // delete user
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('warning', 'flash_message.user_deleted');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->redirectToRoute('admin_users_index');
    }

    /**
     * delete multiple users which are selected and all related entities - AJAX
     */
    #[Route('/delete-multiple', name: 'admin_user_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
        AvatarFileService $avatarFileService,
    ): JsonResponse {
        // Decode the JSON payload from the request body.
        $data = json_decode($request->getContent(), true);

        // Check if the 'ids' key exists and is an array.
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return new JsonResponse(['error' => 'No user IDs provided.'], 400);
        }

        $ids = $data['ids'];

        // Iterate over each ID and remove the corresponding user entity if found.
        foreach ($ids as $id) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found.'], 400);
            }

            if ($currentUser->getId() === $id) {
                return new JsonResponse(['error' => 'You cannot delete yourself.'], 400);
            }

            // delete avatar
            $avatarFileService->deleteAvatar($user->getPhoto(), '/users');

            // delete all users at once
            $entityManager->remove($user);
        }

        // Persist all removals in one go.
        $entityManager->flush();

        // Return a JSON response with status 200 to indicate success.
        return new JsonResponse(['status' => 'success'], 200);
    }

    /**
     * export users to CSV
     * @throws Exception
     */
    #[Route('/export.csv', name: 'admin_users_csv_export', methods: ['GET'])]
    public function exportCoursesToCsv(
        UserRepository $userRepository,
        Request $request,
    ): Response {
        // Fetch data
        $users = $userRepository->findAll();

        // Create a League CSV Writer using an in-memory stream
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());

        // Insert header
        $csvWriter->insertOne([
            'ID',
            'CreatedAt',
            'UpdatedAt',
            'Last Seen',
            'Last Login',
            'Email',
            'First Name',
            'Last Name',
            'Birthday',
            'Phone',
            'Address',
            'Zip',
            'City',
            'Country',
            'About',
            'is Super Admin',
            'is Active',
            'is Email Confirmed',
            'Roles',
        ]);

        // Insert data rows
        foreach ($users as $user) {
            $csvWriter->insertOne([
                $user->getId(),
                $user->getCreated()->format($this->dateFormatService->getDateFormat($request)),
                $user->getUpdated()->format($this->dateFormatService->getDateFormat($request)),
                $user->getLastSeen()?->format($this->dateFormatService->getDateFormat($request)),
                $user->getLastLoggedIn()?->format($this->dateFormatService->getDateFormat($request)),
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getBirthdate()?->format($this->dateFormatService->getDateFormat($request)),
                $user->getPhone(),
                $user->getAddress(),
                $user->getZip(),
                $user->getCity(),
                $user->getCountry()?->getName() ?? 'N/A',
                $user->getAbout(),
                $user->isSuperAdmin() ? 'Yes' : 'No',
                $user->isActive() ? 'Yes' : 'No',
                $user->isEmailConfirmed() ? 'Yes' : 'No',
                implode(', ', $user->getRoles()),
            ]);
        }

        // Convert to string
        $csvContent = $csvWriter->toString();

        // Create response
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="users.csv"');

        return $response;
    }
}