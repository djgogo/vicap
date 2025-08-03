<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller used to manage the admin dashboard.
 */
#[Route('/admin/dashboard')]
#[IsGranted(User::ROLE_ADMIN)]
final class DashboardController extends AbstractController
{
    /**
     * Renders the admin dashboard
     */
    #[Route('/', name: 'admin_dashboard_index', methods: ['GET'])]
    public function index(
        #[CurrentUser] User $user,
        UserRepository $userRepository,
    ): Response
    {
        $onlineCount = $userRepository->getOnlineUsersCount();
        $totalCount = $userRepository->count([]);
        $percentOnline = round(($onlineCount / $totalCount) * 100);

        $unconfirmedCount = $userRepository->count(['isEmailConfirmed' => false]);
        $percentUnconfirmed = round(($unconfirmedCount / $totalCount) * 100);
        $bannedCount = $userRepository->count(['isActive' => false]);
        $percentBanned = round(($bannedCount / $totalCount) * 100);

        $firstDay = new DateTime('first day of this month');
        $lastDay = new DateTime('last day of this month');
        $registeredThisMonth = $userRepository->getRegistrationsCount($firstDay, $lastDay);
        $registeredToday = $userRepository->getRegistrationsCount(new DateTime);

        $registrationsPerMonth = $userRepository->getRegistrationCountPerMonth();
        $latestUsers = $userRepository->getLatestUsers(5);

        return $this->render('admin/dashboard/index.html.twig', [
            'currentUser' => $user,
            'online_count' => $onlineCount,
            'total_count' => $totalCount,
            'percent_online' => $percentOnline,
            'unconfirmed_count' => $unconfirmedCount,
            'percent_unconfirmed' => $percentUnconfirmed,
            'banned_count' => $bannedCount,
            'percent_banned' => $percentBanned,
            'registered_this_month' => $registeredThisMonth,
            'registered_today' => $registeredToday,
            'registrations_by_month' => $registrationsPerMonth,
            'latest_users' => $latestUsers,
            'js_initial_state' => [
                'registrations_per_month' => $registrationsPerMonth
            ]
        ]);
    }

}
