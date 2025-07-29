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

class ChefController extends AbstractController
{
    #[Route('/chef/dashboard', name: 'chef_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();

        $menuCount = $em->getRepository(Menu::class)->count(['chef' => $chef]);
        $dishCount = $em->getRepository(Dish::class)->count(['chef' => $chef]);
        $scheduleCount = $em->getRepository(ChefSchedule::class)->count(['chef' => $chef]);

        return $this->render('chef/dashboard.html.twig', [
            'controller_name' => 'Chef Dashboard',
            'menuCount' => $menuCount,
            'dishCount' => $dishCount,
            'scheduleCount' => $scheduleCount,
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
                    $chefProfile->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload dish image.');
                }
            }
            $em->persist($chefProfile);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('chef_profile');
        }

        return $this->render('chef/profile.html.twig', [
            'form' => $form->createView(),
            'profile' => $chefProfile,
        ]);
    }

    #[Route('/chef/menu/{id}/edit', name: 'chef_menu_edit')]
    public function editMenu(Menu $menu, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($menu->getChef() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this menu.');
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
                    $menu->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image upload failed.');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Menu updated successfully.');
            return $this->redirectToRoute('chef_menu');
        }

        return $this->render('chef/edit-menu.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/chef/menu/{id}/delete', name: 'chef_menu_delete', methods: ['POST'])]
    public function deleteMenu(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        if ($menu->getChef() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this menu.');
        }

        if ($this->isCsrfTokenValid('delete_menu_' . $menu->getId(), $request->request->get('_token'))) {
            $em->remove($menu);
            $em->flush();
            $this->addFlash('success', 'Menu deleted successfully.');
        }

        return $this->redirectToRoute('chef_menu');
    }

    #[Route('/chef/dish/{id}/edit', name: 'chef_dish_edit')]
    public function editDish(Dish $dish, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(DishType::class, $dish);
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

            $em->flush();
            $this->addFlash('success', 'Dish updated successfully.');
            return $this->redirectToRoute('chef_dish');
        }

        return $this->render('chef/edit-dish.html.twig', [
            'form' => $form->createView(),
            'dish' => $dish,
        ]);
    }

    #[Route('/chef/dish/{id}/delete', name: 'chef_dish_delete', methods: ['POST'])]
    public function deleteDish(Dish $dish, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_dish_' . $dish->getId(), $request->request->get('_token'))) {
            $em->remove($dish);
            $em->flush();
            $this->addFlash('success', 'Dish deleted successfully.');
        }

        return $this->redirectToRoute('chef_dish');
    }

    #[Route('/chef/schedule', name: 'chef_schedule')]
    public function schedule(Request $request, EntityManagerInterface $em): Response
    {
        $chef = $this->getUser();
        $existingSchedules = $em->getRepository(ChefSchedule::class)->findBy(['chef' => $chef]);

        $schedule = new ChefSchedule();
        $form = $this->createForm(ChefScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule->setChef($chef);
            $schedule->setCreatedAt(new \DateTimeImmutable());
            $em->persist($schedule);
            $em->flush();

            $this->addFlash('success', 'Schedule saved successfully.');
            return $this->redirectToRoute('chef_schedule');
        }

        return $this->render('chef/schedule.html.twig', [
            'form' => $form->createView(),
            'schedules' => $existingSchedules,
        ]);
    }



}