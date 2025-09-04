<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\SupportMessage;
use App\Entity\User;
use App\Entity\Menu;
use App\Entity\Dish;
use App\Entity\ChefSchedule;
use App\Form\DishType;
use App\Form\MenuType;
use App\Repository\ChefProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\UserType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Settings;
use App\Form\SettingsType;

class AdminController extends AbstractController
{

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $userRepo     = $em->getRepository(User::class);
        $menuRepo     = $em->getRepository(Menu::class);
        $dishRepo     = $em->getRepository(Dish::class);
        $scheduleRepo = $em->getRepository(ChefSchedule::class);
        $orderRepo    = $em->getRepository(reservation::class);

        $totalUsers = count($userRepo->findAll());
        $totalChefs = count($userRepo->findBy(['role' => 'chef']));
        $totalMenus = count($menuRepo->findAll());
        $totalDishes = count($dishRepo->findAll());
        $totalSchedules = count($scheduleRepo->findAll());
        $totalorders = count($orderRepo->findAll());

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalChefs' => $totalChefs,
            'totalMenus' => $totalMenus,
            'totalDishes' => $totalDishes,
            'totalSchedules' => $totalSchedules,
            'totalorders' => $totalorders,
        ]);
    }

    #[Route('/admin/chefs', name: 'admin_chefs')]
    public function listChefs(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $sql = "
        SELECT 
            cp.id AS id, 
            u.id AS user_id,
            u.name,
            u.email,
            cp.bio,
            cp.certification,
            cp.image,
            cp.approved,
            cp.created_at
        FROM chef_profile cp
        JOIN user u ON u.id = cp.user_id
    ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $chefs = $result->fetchAllAssociative();

        return $this->render('admin/chefs.html.twig', [
            'chefs' => $chefs
        ]);
    }


    #[Route('/admin/menus', name: 'admin_menus')]
    public function allMenus(EntityManagerInterface $em): Response
    {
        $menus = $em->getRepository(Menu::class)->findAll();
        return $this->render('admin/menus.html.twig', ['menus' => $menus]);
    }

    #[Route('/admin/menu/{id}/edit', name: 'admin_menu_edit')]
    public function editMenu(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $menu->setImage($newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Menu updated successfully.');
                return $this->redirectToRoute('admin_menus');
            } else {
                $this->addFlash('error', 'Please correct the errors in the form.');
            }
        }

        return $this->render('admin/edit_menu.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu
        ]);
    }

    #[Route('/admin/menu/{id}/delete', name: 'admin_menu_delete', methods: ['POST'])]
    public function deleteMenu(Menu $menu, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_menu_' . $menu->getId(), $request->request->get('_token'))) {
            $em->remove($menu);
            $em->flush();
            $this->addFlash('success', 'Menu deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Menu could not be deleted.');
        }

        return $this->redirectToRoute('admin_menus');
    }


    #[Route('/admin/dishes', name: 'admin_dishes')]
    public function allDishes(EntityManagerInterface $em): Response
    {
        $dishes = $em->getRepository(Dish::class)->findAll();
        return $this->render('admin/dishes.html.twig', ['dishes' => $dishes]);
    }

    #[Route('/admin/dish/{id}/edit', name: 'admin_dish_edit')]
    public function edit(Request $request, Dish $dish, EntityManagerInterface $em): Response
    {
        $chef = $dish->getMenu()?->getChef();
        $form = $this->createForm(DishType::class, $dish, ['chef' => $chef]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    $dish->setImage($newFilename);
                }

                $em->flush();
                $this->addFlash('success', 'Dish updated successfully!');
                return $this->redirectToRoute('admin_dishes');
            } else {
                $this->addFlash('error', 'Please correct the errors in the dish form.');
            }
        }

        return $this->render('admin/edit_dish.html.twig', [
            'form' => $form->createView(),
            'dish' => $dish
        ]);
    }


    #[Route('/admin/dish/{id}/delete', name: 'admin_dish_delete', methods: ['POST'])]
    public function delete(Request $request, Dish $dish, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_dish_' . $dish->getId(), $request->request->get('_token'))) {
            $em->remove($dish);
            $em->flush();
            $this->addFlash('success', 'Dish deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Dish could not be deleted.');
        }

        return $this->redirectToRoute('admin_dishes');
    }


    #[Route('/admin/schedules', name: 'admin_schedules')]
    public function allSchedules(EntityManagerInterface $em): Response
    {
        $schedules = $em->getRepository(ChefSchedule::class)->findAll();
        return $this->render('admin/schedules.html.twig', ['schedules' => $schedules]);
    }

    #[Route('/admin/schedule/delete/{id}', name: 'admin_schedule_delete')]
    public function deleteSchedule(int $id, EntityManagerInterface $em): RedirectResponse
    {
        $schedule = $em->getRepository(ChefSchedule::class)->find($id);

        if (!$schedule) {
            $this->addFlash('error', 'Schedule not found.');
            return $this->redirectToRoute('admin_schedules');
        }

        $em->remove($schedule);
        $em->flush();
        $this->addFlash('success', 'Schedule deleted successfully.');
        return $this->redirectToRoute('admin_schedules');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', ['users' => $users]);
    }

    #[Route('/admin/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'User updated successfully.');
                return $this->redirectToRoute('admin_users');
            } else {
                $this->addFlash('error', 'Please correct the errors in the user form.');
            }
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. User could not be deleted.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/chef/{id}', name: 'admin_chef_profile_view')]
    public function viewChefProfile(int $id, ChefProfileRepository $chefProfileRepo): Response
    {
        $profile = $chefProfileRepo->find($id);

        if (!$profile) {
            throw $this->createNotFoundException('Chef profile not found.');
        }

        return $this->render('admin/chef_profile_view.html.twig', [
            'profile' => $profile,
        ]);
    }

    #[Route('/admin/chef/{id}/approve', name: 'admin_chef_profile_approve')]
    public function approveChefProfile(int $id, EntityManagerInterface $em, ChefProfileRepository $chefProfileRepo): Response {
        $profile = $chefProfileRepo->find($id);
        if (!$profile) {
            throw $this->createNotFoundException('Chef profile not found.');
        }
        $profile->setApproved(true);
        $em->flush();
        $this->addFlash('success', 'Chef has been approved successfully.');
        return $this->redirectToRoute('admin_chef_profile_view', ['id' => $id]);
    }

    #[Route('/admin/chef/{id}/reject', name: 'admin_chef_profile_reject')]
    public function rejectChefProfile(int $id, EntityManagerInterface $em, ChefProfileRepository $chefProfileRepo): Response {
        $profile = $chefProfileRepo->find($id);
        if (!$profile) {
            throw $this->createNotFoundException('Chef profile not found.');
        }
        $profile->setApproved(false);
        $em->flush();
        $this->addFlash('success', 'Chef has been rejected successfully.');
        return $this->redirectToRoute('admin_chef_profile_view', ['id' => $id]);
    }

    #[Route('/admin/support', name: 'admin_support')]
    public function support(EntityManagerInterface $em): Response
    {
        $messages = $em->getRepository(SupportMessage::class)->findBy([
            'receiverType' => ['admin', null],
        ], ['createdAt' => 'DESC']);

        return $this->render('admin/support.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/admin/support/{id}/reply', name: 'admin_support_reply', methods: ['POST'])]
    public function replySupport(Request $request, SupportMessage $message, EntityManagerInterface $em): Response
    {
        $admin = $this->getUser();

        if ($message->getReceiverType() !== null && $message->getReceiverType() !== 'admin') {
            throw $this->createAccessDeniedException();
        }

        $reply = $request->request->get('reply');
        if ($reply) {
            $message->setReply($reply);
            $message->setStatus('answered');
            $em->flush();
            $this->addFlash('success', 'Reply sent successfully!');
        }

        return $this->redirectToRoute('admin_support');
    }

    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(Request $request, EntityManagerInterface $em): Response
    {
        $settingsRepo = $em->getRepository(Settings::class);

        $settings = $settingsRepo->findOneBy([]);
        if (!$settings) {
            $settings = new Settings();
            $settings->setTax(0);
        }

        $form = $this->createForm(SettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($settings);
            $em->flush();
            $this->addFlash('success', 'Settings updated successfully!');
            return $this->redirectToRoute('admin_settings');
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
            'settings' => $settings
        ]);
    }

    #[Route('/admin/orders', name: 'admin_orders')]
    public function allOrders(EntityManagerInterface $em): Response
    {
        $orders = $em->getRepository(Reservation::class)->findAll();
        return $this->render('admin/orders.html.twig', ['orders' => $orders]);
    }

    #[Route('/admin/order/{id}/delete', name: 'admin_order_delete', methods: ['POST'])]
    public function deleteOrder(Reservation $order, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_order_' . $order->getId(), $request->request->get('_token'))) {
            $em->remove($order);
            $em->flush();
            $this->addFlash('success', 'Order deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Order could not be deleted.');
        }

        return $this->redirectToRoute('admin_orders');
    }

    #[Route('/force-error')]
    public function forceError()
    {
        throw $this->createNotFoundException("Custom 404 test");
    }



}
