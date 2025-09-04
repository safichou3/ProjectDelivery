<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Menu;
use App\Form\MenuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Dish;
use App\Form\DishType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\ChefProfile;
use App\Form\ChefProfileType;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\ChefSchedule;
use App\Form\ChefScheduleType;
use App\Entity\Reservation;
use App\Entity\SupportMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ChefController extends AbstractController
{
    #[Route('/chef/dashboard', name: 'chef_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();

        $menuCount = $em->getRepository(Menu::class)->count(['chef' => $chef]);
        $dishCount = $em->getRepository(Dish::class)->count(['chef' => $chef]);
        $scheduleCount = $em->getRepository(ChefSchedule::class)->count(['chef' => $chef]);
        $profile = $em->getRepository(ChefProfile::class)->findOneBy(['user' => $chef]);
        $needsUpdate = !$profile || !$profile->getLicence() || !$profile->getImage();
        $notApproved = $profile && !$profile->isApproved();

        $pendingOrdersCount = $em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->join('r.dish', 'd')
            ->join('d.menu', 'm')
            ->where('m.chef = :chef')
            ->andWhere('r.status = :status')
            ->setParameter('chef', $chef)
            ->setParameter('status', 'pending')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('chef/dashboard.html.twig', [
            'controller_name' => 'Chef Dashboard',
            'menuCount' => $menuCount,
            'dishCount' => $dishCount,
            'scheduleCount' => $scheduleCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'needsProfileUpdate' => $needsUpdate,
            'notApproved' => $notApproved,
        ]);
    }

    #[Route('/chef/menu/add', name: 'chef_menu_add')]
    public function addMenu(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $menu = new Menu();
        $menu->setChef($this->getUser());
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile){
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension() ?? 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->redirectToRoute('chef_menu_add');
                }

                $menu->setImage($newFilename);
            }

            $em->persist($menu);
            $em->flush();
            $this->addFlash('success', 'Menu created successfully!');

            return $this->redirectToRoute('chef_menu');
        }

        return $this->render('chef/add-menu.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/chef/dish/add', name: 'chef_dish_add')]
    public function addDish(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $dish = new Dish();
        $chef = $this->getUser();
        $form = $this->createForm(DishType::class, $dish, [
            'chef' => $chef,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension() ?? 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $dish->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload dish image.');
                }
            }
            $dish->setChef($this->getUser());
            $dish->setCreatedAt(new \DateTimeImmutable());
            $em->persist($dish);
            $em->flush();
            $this->addFlash('success', 'Dish added successfully!');
            return $this->redirectToRoute('chef_dish');
        }

        return $this->render('chef/add-dish.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/chef/menu', name: 'chef_menu')]
    public function menuList(EntityManagerInterface $em): Response
    {
        $menus = $em->getRepository(Menu::class)->findBy(['chef' => $this->getUser()]);

        return $this->render('chef/menu.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/chef/dish', name: 'chef_dish')]
    public function dishList(EntityManagerInterface $em): Response
    {
        $dishes = $em->getRepository(Dish::class)->findBy(['chef' => $this->getUser()]);

        return $this->render('chef/dish.html.twig', [
            'dishes' => $dishes,
        ]);
    }

    #[Route('/chef/profile', name: 'chef_profile')]
    public function profile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $chefProfile = $em->getRepository(ChefProfile::class)->findOneBy(['user' => $user]);

        if (!$chefProfile) {
            $chefProfile = new ChefProfile();
            $chefProfile->setUser($user);
            $chefProfile->setCreatedAt(new \DateTimeImmutable());
        }

        $form = $this->createForm(ChefProfileType::class, $chefProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            $licenceFile = $form->get('licence')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension() ?? 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->redirectToRoute('chef_profile');
                }

                $chefProfile->setImage($newFilename);
            }

            if ($licenceFile) {
                $originalFilename = pathinfo($licenceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $licenceFile->guessExtension() ?? 'bin';

                $newFilename = 'licence-' . $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $licenceFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload licence file.');
                    return $this->redirectToRoute('chef_profile');
                }

                $chefProfile->setLicence($newFilename);
            }
            $em->persist($chefProfile);
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('chef_profile');
        }

        return $this->render('chef/profile.html.twig', [
            'form' => $form->createView(),
            'profile' => $chefProfile,
        ]);
    }

    #[Route('/chef/schedule', name: 'chef_schedule')]
    public function schedule(Request $request, EntityManagerInterface $em): Response
    {
        $schedule = new ChefSchedule();
        $schedule->setChef($this->getUser());

        $form = $this->createForm(ChefScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($schedule);
            $em->flush();
            $this->addFlash('success', 'Schedule created successfully!');
            return $this->redirectToRoute('chef_schedule');
        }

        $schedules = $em->getRepository(ChefSchedule::class)->findBy(['chef' => $this->getUser()]);

        return $this->render('chef/schedule.html.twig', [
            'form' => $form->createView(),
            'schedules' => $schedules,
        ]);
    }
    #[Route('/chef/schedule/{id}/delete', name: 'chef_schedule_delete', methods: ['POST'])]
    public function deleteSchedule(Request $request, ChefSchedule $schedule, EntityManagerInterface $em): Response
    {
        if ($schedule->getChef() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_schedule_' . $schedule->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('chef_schedule');
        }

        $em->remove($schedule);
        $em->flush();
        $this->addFlash('success', 'Schedule deleted successfully.');
        return $this->redirectToRoute('chef_schedule');
    }

    #[Route('/chef/orders', name: 'chef_orders')]
    public function orders(EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();
        $orders = $em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->join('r.dish', 'd')
            ->addSelect('d')
            ->join('d.menu', 'm')
            ->join('r.client', 'c')
            ->where('m.chef = :chef')
            ->setParameter('chef', $chef)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('chef/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/chef/order/{id}/status', name: 'chef_order_update_status', methods: ['POST'])]
    public function updateOrderStatus(Request $request, Reservation $reservation, EntityManagerInterface $em): RedirectResponse
    {
        $chef = $this->getUser();
        if ($reservation->getDish()->getMenu()->getChef() !== $chef) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('order_status_' . $reservation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('chef_orders');
        }

        $status = $request->request->get('status');
        $allowed = ['pending', 'in_progress', 'delivered'];
        if (!in_array($status, $allowed, true)) {
            $this->addFlash('error', 'Invalid status provided.');
            return $this->redirectToRoute('chef_orders');
        }
        if (strtolower($reservation->getStatus()) === 'cancelled') {
            $this->addFlash('error', 'Cancelled orders cannot be updated.');
            return $this->redirectToRoute('chef_orders');
        }

        $reservation->setStatus($status);
        $em->flush();
        $this->addFlash('success', 'Order status updated to ' . str_replace('_', ' ', $status) . '.');
        return $this->redirectToRoute('chef_orders');
    }

    #[Route('/chef/menu/{id}/edit', name: 'chef_menu_edit')]
    public function editMenu(Request $request, Menu $menu, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($menu->getChef() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension() ?? 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }

                $menu->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Menu updated successfully!');

            return $this->redirectToRoute('chef_menu');
        }

        return $this->render('chef/edit-menu.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/chef/menu/{id}/delete', name: 'chef_menu_delete', methods: ['POST'])]
    public function deleteMenu(Request $request, Menu $menu, EntityManagerInterface $em): Response
    {
        if ($menu->getChef() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_menu_' . $menu->getId(), $request->request->get('_token'))) {
            $em->remove($menu);
            $em->flush();
            $this->addFlash('success', 'Menu deleted successfully!');
        }

        return $this->redirectToRoute('chef_menu');
    }


    #[Route('/chef/dish/{id}/edit', name: 'chef_dish_edit')]
    public function editDish(Request $request, Dish $dish, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $chef = $this->getUser();
        $form = $this->createForm(DishType::class, $dish, [
            'chef' => $chef,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension() ?? 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $dish->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload dish image: ' . $e->getMessage());
                    return $this->redirectToRoute('chef_dish_edit', ['id' => $dish->getId()]);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Dish updated successfully!');
            return $this->redirectToRoute('chef_dish');
        }

        return $this->render('chef/edit-dish.html.twig', [
            'form' => $form->createView(),
            'dish' => $dish,
        ]);
    }


    #[Route('/chef/dish/{id}/delete', name: 'chef_dish_delete', methods: ['POST'])]
    public function deleteDish(Request $request, Dish $dish, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_dish_' . $dish->getId(), $request->request->get('_token'))) {
            $em->remove($dish);
            $em->flush();
        }

        return $this->redirectToRoute('chef_dish');
    }

    #[Route('/chef/support', name: 'chef_support')]
    public function supportMessages(EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();

        $messages = $em->getRepository(SupportMessage::class)->findBy([
            'receiverType' => 'chef',
            'receiverId' => $chef->getId(),
        ], ['createdAt' => 'DESC']);

        return $this->render('chef/support.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/chef/support/{id}/reply', name: 'chef_support_reply', methods: ['POST'])]
    public function replySupport(Request $request, SupportMessage $message, EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();

        if ($message->getReceiverType() !== 'chef' || $message->getReceiverId() !== $chef->getId()) {
            throw $this->createAccessDeniedException();
        }

        $reply = $request->request->get('reply');
        if ($reply) {
            $message->setReply($reply);
            $message->setStatus('answered');
            $em->flush();
            $this->addFlash('success', 'Reply sent successfully!');
        }

        return $this->redirectToRoute('chef_support');
    }

    #[Route('/chef/order/{id}/client', name: 'chef_order_client_info')]
    public function orderClientInfo(Reservation $order): Response
    {
        $chef = $this->getUser();
        if ($order->getDish()->getMenu()->getChef() !== $chef) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('chef/order_client_info.html.twig', [
            'order' => $order,
            'client' => $order->getClient(),
        ]);
    }



}