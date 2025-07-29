<?php

namespace App\Controller;

use App\Repository\DishRepository;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('user/dashboard.html.twig', [
            'controller_name' => 'User Dashboard',
        ]);
    }

    #[Route('/api/chefs', name: 'api_get_chefs', methods: ['GET'])]
    public function getApprovedChefs(UserRepository $userRepository): JsonResponse
    {
        $chefs = $userRepository->findBy(['role' => 'chef']);
        $data = [];
        foreach ($chefs as $chef) {
            $profile = $chef->getChefProfile();
            if ($profile && $profile->isApproved()) {
                $data[] = [
                    'id' => $chef->getId(),
                    'name' => $chef->getName(),
                    'email' => $chef->getEmail(),
                    'bio' => $profile->getBio(),
                    'image' => $profile->getImage() ? '/uploads/' . $profile->getImage() : null,
                ];
            }
        }

        return $this->json($data);
    }

    #[Route('/api/chefs/{id}/menus', name: 'api_get_chef_menus', methods: ['GET'])]
    public function getChefMenus(int $id, MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findBy(['chef' => $id, 'isActive' => true]);
        $data = [];
        foreach ($menus as $menu) {
            $data[] = [
                'id' => $menu->getId(),
                'title' => $menu->getTitle(),
                'description' => $menu->getDescription(),
                'price' => $menu->getPrice(),
                'availableFrom' => $menu->getAvailableFrom()?->format('Y-m-d'),
                'availableTo' => $menu->getAvailableTo()?->format('Y-m-d'),
            ];
        }

        return $this->json($data);
    }

    /**
     * @param int $id
     * @param DishRepository $dishRepository
     * @return JsonResponse
     */
    #[Route('/api/menus/{id}/dishes', name: 'api_get_menu_dishes', methods: ['GET'])]
    public function getMenuDishes(int $id, DishRepository $dishRepository): JsonResponse
    {
        $dishes = $dishRepository->findBy(['menu' => $id]);

        $data = [];

        foreach ($dishes as $dish) {
            $data[] = [
                'id' => $dish->getId(),
                'name' => $dish->getName(),
                'description' => $dish->getDescription(),
                'image' => $dish->getImage() ? '/uploads/' . $dish->getImage() : null,
            ];
        }

        return $this->json($data);
    }



}