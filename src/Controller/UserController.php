<?php

namespace App\Controller;

use App\Repository\ChefScheduleRepository;
use App\Repository\DishRepository;
use App\Repository\MenuRepository;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use App\Repository\ChefProfileRepository;
use App\Entity\ChefSchedule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\SupportMessage;
use App\Repository\SupportMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Types\Types;
use App\Entity\FavoriteDish;
use App\Repository\FavoriteDishRepository;
use App\Entity\Reservation;
use App\Entity\Dish;
use App\Entity\Review;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


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
    public function getApprovedChefs(ChefProfileRepository $chefProfileRepository): JsonResponse
    {
        $approvedProfiles = $chefProfileRepository->findBy(['approved' => true]);

        $data = [];

        foreach ($approvedProfiles as $profile) {
            $user = $profile->getUser();

            $data[] = [
                'id'            => $profile->getId(),
                'user_id'       => $user->getId(),
                'name'          => $user->getName(),
                'email'         => $user->getEmail(),
                'bio'           => $profile->getBio(),
                'approved'      => $profile->isApproved(),
                'certification' => $profile->getCertification(),
                'image'         => $profile->getImage() ? '/uploads/' . $profile->getImage() : null,
                'licence'       => $profile?->getLicence() ? '/uploads/' . $profile->getLicence() : null,
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/chefs/available', name: 'api_available_chefs', methods: ['GET'])]
    public function availableChefs(Request $request, ChefScheduleRepository $scheduleRepo): JsonResponse
    {
        $dateParam = $request->query->get('datetime');
        $dateTime = new \DateTime($dateParam);

        $availableChefs = $scheduleRepo->findAvailableChefs($dateTime);
        $data = [];
        foreach ($availableChefs as $schedule) {
            $chef = $schedule->getChef();
            $data[] = [
                'id' => $chef->getId(),
                'name' => $chef->getName(),
                'user_id' => $chef->getId(),
                'email'   => $chef->getEmail(),
                'openingTime' => $schedule->getOpeningTime()->format('H:i'),
                'closingTime' => $schedule->getClosingTime()->format('H:i'),
                'bio' => $chef->getChefProfile()->getBio(),
                'certification' => $chef->getChefProfile()->getCertification(),
                'image'         => $chef->getChefProfile()->getImage() ? '/uploads/' . $chef->getChefProfile()->getImage() : null,
                'licence'       => $chef->getChefProfile()->getLicence() ? '/uploads/' . $chef->getChefProfile()->getLicence() : null,
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/chefs/{id}/menus', name: 'api_get_chef_menus', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function getChefMenus(string $id, MenuRepository $menuRepository): JsonResponse
    {
        $chefId = (int) $id;
        $menus = $menuRepository->findBy(['chef' => $chefId, 'isActive' => true]);
        $data = [];
        foreach ($menus as $menu) {
            $data[] = [
                'id' => $menu->getId(),
                'title' => $menu->getTitle(),
                'description' => $menu->getDescription(),
                'cuisineType' => $menu->getCuisineType(),
                'availableFrom' => $menu->getAvailableFrom()?->format('Y-m-d'),
                'availableTo' => $menu->getAvailableTo()?->format('Y-m-d'),
                'image'    => $menu?->getImage() ? '/uploads/' . $menu->getImage() : null,
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
                'price' => $dish->getPrice(),
                'image' => $dish->getImage() ? '/uploads/' . $dish->getImage() : null,
                'menuId' => $dish->getMenu()?->getId(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/dishes/{id}', name: 'api_get_dish', methods: ['GET'])]
    public function getDish(int $id, DishRepository $dishRepository, ChefProfileRepository $chefProfileRepository): JsonResponse
    {
        $dish = $dishRepository->find($id);
        if (!$dish) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $menu = $dish->getMenu();
        if ($menu) {
            if (!$menu->isIsActive()) {
                return $this->json(['error' => 'Dish not available'], 404);
            }

            $currentDate = new \DateTime();
            $availableFrom = $menu->getAvailableFrom();
            $availableTo = $menu->getAvailableTo();
            
            if ($availableFrom && $currentDate < $availableFrom) {
                return $this->json(['error' => 'Dish not available yet'], 404);
            }
            
            if ($availableTo && $currentDate > $availableTo) {
                return $this->json(['error' => 'Dish no longer available'], 404);
            }

            $chef = $menu->getChef();
            $chefProfile = $chefProfileRepository->findOneBy(['user' => $chef]);
            
            if (!$chefProfile || !$chefProfile->isApproved()) {
                return $this->json(['error' => 'Dish not available'], 404);
            }
        }

        return $this->json([
            'id' => $dish->getId(),
            'name' => $dish->getName(),
            'description' => $dish->getDescription(),
            'price' => $dish->getPrice(),
            'image' => $dish->getImage() ? '/uploads/'.$dish->getImage() : null,
            'menuId' => $dish->getMenu()?->getId(),
            'ingredients' => $dish->getIngredients(),
        ]);
    }

    #[Route('/api/dishes', name: 'api_get_all_dishes', methods: ['GET'])]
    public function getAllDishes(Request $request, DishRepository $dishRepository, ChefScheduleRepository $chefScheduleRepository, ChefProfileRepository $chefProfileRepository): JsonResponse {

        $dateParam = $request->query->get('datetime');
        $now = $dateParam ? new \DateTimeImmutable($dateParam) : new \DateTimeImmutable();
        $allDishes = $dishRepository->findAll();
        $data = [];

        foreach ($allDishes as $dish) {
            $menu = $dish->getMenu();
            if (!$menu) {
                continue;
            }

            if (!$menu->isIsActive()) {
                continue;
            }

            $availableFrom = $menu->getAvailableFrom();
            $availableTo   = $menu->getAvailableTo();
            if ($availableFrom && $now < $availableFrom) {
                continue;
            }
            if ($availableTo && $now > $availableTo) {
                continue;
            }

            $chef = $menu->getChef();
            if (!$chef) {
                continue;
            }

            $chefProfile = $chefProfileRepository->findOneBy(['user' => $chef]);
            if (!$chefProfile || !$chefProfile->isApproved()) {
                continue;
            }

            $availableChefs = $chefScheduleRepository->findAvailableChefs($now);
            $isChefAvailable = false;
            foreach ($availableChefs as $schedule) {
                if ($schedule->getChef()->getId() === $chef->getId()) {
                    $isChefAvailable = true;
                    break;
                }
            }
            if (!$isChefAvailable) {
                continue;
            }

            $data[] = [
                'id'          => $dish->getId(),
                'menu_id'     => $menu->getId(),
                'chef_id'     => $chef->getId(),
                'name'        => $dish->getName(),
                'description' => $dish->getDescription(),
                'price'       => $dish->getPrice(),
                'image'       => $dish->getImage() ? '/uploads/' . $dish->getImage() : null,
                'created_at'  => $dish->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }



    #[Route('/api/dishes/related/{id}', name: 'api_get_related_dishes', methods: ['GET'])]
    public function getRelatedDishes(int $id, DishRepository $dishRepository, MenuRepository $menuRepository, ChefProfileRepository $chefProfileRepository, Request $request): JsonResponse
    {
        $currentDish = $dishRepository->find($id);
        if (!$currentDish) {
            return $this->json(['error' => 'Dish not found'], 404);
        }

        $cuisineType = $request->query->get('cuisine');
        $chefId = $request->query->get('chef');
        $limit = (int) ($request->query->get('limit') ?? 4);
        $currentDate = new \DateTime();
        $qb = $dishRepository->createQueryBuilder('d')
            ->join('d.menu', 'm')
            ->join('m.chef', 'c')
            ->join('c.chefProfile', 'cp')
            ->where('d.id != :currentId')
            ->andWhere('cp.approved = :approved')
            ->andWhere('m.isActive = :isActive')
            ->andWhere('(m.availableFrom IS NULL OR m.availableFrom <= :currentDate)')
            ->andWhere('(m.availableTo IS NULL OR m.availableTo >= :currentDate)')
            ->setParameter('currentId', $id)
            ->setParameter('approved', true)
            ->setParameter('isActive', true)
            ->setParameter('currentDate', $currentDate);

        if ($cuisineType && !empty($cuisineType)) {
            $qb->andWhere('m.cuisineType = :cuisine')
                ->setParameter('cuisine', $cuisineType);
        }

        if ($chefId && !empty($chefId)) {
            $qb->andWhere('c.id = :chefId')
                ->setParameter('chefId', $chefId);
        }

        if (!$cuisineType && !$chefId && $currentDish->getMenu() && $currentDish->getMenu()->getChef()) {
            $qb->andWhere('c.id = :sameChefId')
                ->setParameter('sameChefId', $currentDish->getMenu()->getChef()->getId());
        }

        $relatedDishes = $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($relatedDishes as $dish) {
            $data[] = [
                'id' => $dish->getId(),
                'name' => $dish->getName(),
                'description' => $dish->getDescription(),
                'price' => $dish->getPrice(),
                'image' => $dish->getImage() ? '/uploads/' . $dish->getImage() : null,
                'cuisineType' => $dish->getMenu()?->getCuisineType(),
                'menuId' => $dish->getMenu()?->getId(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/support-messages', name: 'api_get_support_messages', methods: ['GET'])]
    public function getSupportMessages(SupportMessageRepository $supportMessageRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if (in_array('ROLE_CHEF', $user->getRoles())) {
            $messages = $supportMessageRepository->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );
        } else {
            $messages = $supportMessageRepository->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );
        }

        $data = [];
        foreach ($messages as $msg) {
            $data[] = [
                'id'          => $msg->getId(),
                'subject'     => $msg->getSubject(),
                'message'     => $msg->getMessage(),
                'reply'       => $msg->getReply(),
                'status'      => $msg->getStatus(),
                'receiverType'=> $msg->getReceiverType(),
                'receiverId'  => $msg->getReceiverId(),
                'createdAt'   => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }


    #[Route('/api/support-messages', name: 'api_create_support_message', methods: ['POST'])]
    public function createSupportMessage(Request $request, EntityManagerInterface $em): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['subject']) || !isset($data['message']) || !isset($data['receiverType'])) {
            return $this->json(['error' => 'Subject, message and receiverType are required'], 400);
        }

        $supportMessage = new SupportMessage();
        $supportMessage->setSubject($data['subject']);
        $supportMessage->setMessage($data['message']);
        $supportMessage->setReply(null);
        $supportMessage->setStatus('open');
        $supportMessage->setUser($user);
        $supportMessage->setCreatedAt(new \DateTimeImmutable());

        $supportMessage->setReceiverType($data['receiverType']);
        $supportMessage->setReceiverId($data['receiverId'] ?? null);

        $em->persist($supportMessage);
        $em->flush();

        return $this->json([
            'success'      => true,
            'id'           => $supportMessage->getId(),
            'subject'      => $supportMessage->getSubject(),
            'message'      => $supportMessage->getMessage(),
            'reply'        => $supportMessage->getReply(),
            'receiverType' => $supportMessage->getReceiverType(),
            'receiverId'   => $supportMessage->getReceiverId(),
            'createdAt'    => $supportMessage->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
    #[Route('/api/user', name: 'api_get_user', methods: ['GET'])]
    public function getUserData(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id'        => $user->getId(),
            'name'      => $user->getName(),
            'email'     => $user->getEmail(),
            'phone'     => $user->getPhoneNumber(),
            'country'   => $user->getCountry(),
            'city'      => $user->getCity(),
            'postalCode'=> $user->getPostalCode(),
            'address'   => $user->getAddress(),
        ]);
    }

    #[Route('/api/user/location', name: 'api_update_user_location', methods: ['PUT'])]
    public function updateUserLocation(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['country'])) $user->setCountry($data['country']);
        if (isset($data['address'])) $user->setAddress($data['address']);
        if (isset($data['city'])) $user->setCity($data['city']);
        if (isset($data['postalCode'])) $user->setPostalCode($data['postalCode']);

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'location' => [
                'country'    => $user->getCountry(),
                'address'    => $user->getAddress(),
                'city'       => $user->getCity(),
                'postalCode' => $user->getPostalCode(),
            ]
        ]);
    }

    #[Route('/api/user/update', name: 'api_update_user', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank()],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'phone_number' => [new Assert\NotBlank()],
            'password' => [
                new Assert\Optional([
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Password must be at least {{ limit }} characters long.'
                    ])
                ])
            ]
        ]);

        $errors = $validator->validate($data, $constraints);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->json(['error' => $errorMessages], 400);
        }


        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhoneNumber($data['phone_number']);

        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
        ]);
    }

    #[Route('/api/user/favorites', name: 'api_get_favorites', methods: ['GET'])]
    public function getFavorites(FavoriteDishRepository $favRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $favorites = $favRepo->findBy(['user' => $user]);
        $data = [];

        foreach ($favorites as $fav) {
            $dish = $fav->getDish();
            $data[] = [
                'id' => $dish->getId(),
                'name' => $dish->getName(),
                'price' => $dish->getPrice(),
                'image' => $dish->getImage() ? '/uploads/' . $dish->getImage() : null,
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/user/favorites/toggle', name: 'api_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(Request $request, EntityManagerInterface $em, DishRepository $dishRepo, FavoriteDishRepository $favRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['dishId'])) {
            return $this->json(['error' => 'Dish ID is required'], 400);
        }

        $dish = $dishRepo->find($data['dishId']);
        if (!$dish) {
            return $this->json(['error' => 'Dish not found'], 404);
        }

        $favorite = $favRepo->findOneBy(['user' => $user, 'dish' => $dish]);

        if ($favorite) {
            $em->remove($favorite);
            $em->flush();
            return $this->json(['success' => true, 'action' => 'removed', 'message' => 'Dish removed from favorites']);
        } else {
            $favorite = new FavoriteDish();
            $favorite->setUser($user);
            $favorite->setDish($dish);

            $em->persist($favorite);
            $em->flush();

            return $this->json(['success' => true, 'action' => 'added', 'message' => 'Dish added to favorites']);
        }
    }

    #[Route('/api/reservations', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['cartItems']) || !isset($data['paymentStatus'])) {
            return new JsonResponse(['error' => 'cartItems and paymentStatus are required'], 400);
        }

        $paymentType = strtolower($data['paymentStatus']) === 'online' ? 'online' : 'cod';
        $paymentStatus = $paymentType === 'online' ? 'unpaid' : 'unpaid';
        $discountAmount = $data['discountAmount'] ?? 0;
        $taxRate = floatval($data['taxRate'] ?? 0);

        $createdReservations = [];

        foreach ($data['cartItems'] as $item) {
            if (!isset($item['id'], $item['quantity'], $item['price'], $item['date'])) {
                continue;
            }

            $dish = $em->getRepository(Dish::class)->find($item['id']);
            if (!$dish) {
                return new JsonResponse(['error' => 'Dish not found: '.$item['id']], 404);
            }

            $quantity = intval($item['quantity']);
            $unitPrice = floatval($item['price']);
            $subTotal = $unitPrice * $quantity;

            $itemDiscount = 0;
            if ($discountAmount > 0) {
                $totalCartAmount = array_reduce($data['cartItems'], fn($sum, $i) => $sum + ($i['price'] * $i['quantity']), 0);
                $itemDiscount = round($subTotal / $totalCartAmount * $discountAmount, 2);
            }

            $taxAmount = round(($subTotal - $itemDiscount) * ($taxRate / 100), 2);
            $totalAmount = $subTotal - $itemDiscount + $taxAmount;

            $pickupDate = new \DateTime($item['date']);

            $reservation = new Reservation();
            $reservation->setClient($user);
            $reservation->setDish($dish);
            $reservation->setPickupDate($pickupDate);
            $reservation->setQuantity($quantity);
            $reservation->setSubTotal($subTotal);
            $reservation->setDiscountAmount($itemDiscount);
            $reservation->setTaxAmount($taxAmount);
            $reservation->setTotalAmount($totalAmount);
            $reservation->setPaymentType($paymentType);
            $reservation->setPaymentStatus($paymentStatus);
            $reservation->setStatus('pending');

            $em->persist($reservation);
            $createdReservations[] = $reservation;
        }

        $em->flush();

        return new JsonResponse([
            'message' => 'Reservations created successfully',
            'reservations' => array_map(fn($r) => [
                'id' => $r->getId(),
                'dish' => $r->getDish()->getName(),
                'quantity' => $r->getQuantity(),
                'totalAmount' => $r->getTotalAmount(),
                'pickupDate' => $r->getPickupDate()->format('Y-m-d'),
                'status' => $r->getStatus(),
                'paymentType' => $r->getPaymentType(),
                'paymentStatus' => $r->getPaymentStatus(),
            ], $createdReservations)
        ], 201);
    }


    #[Route('/api/update-payment-status', name: 'update_payment_status', methods: ['POST'])]
    public function updatePaymentStatus(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['orderIds']) || !isset($data['sessionId'])) {
            return new JsonResponse(['error' => 'orderIds and sessionId are required'], 400);
        }

        $orderIds = $data['orderIds'];
        $sessionId = $data['sessionId'];

        $updatedCount = 0;

        foreach ($orderIds as $orderId) {
            $reservation = $em->getRepository(Reservation::class)->find($orderId);
            
            if ($reservation && $reservation->getClient()->getId() === $user->getId()) {
                $reservation->setPaymentStatus('paid');
                $em->persist($reservation);
                $updatedCount++;
            }
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Payment status updated for {$updatedCount} orders",
            'updatedCount' => $updatedCount
        ]);
    }

    #[Route('/api/my-orders', name: 'my_orders', methods: ['GET'])]
    public function myOrders(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $reservations = $em->getRepository(Reservation::class)->findBy(
            ['client' => $user],
            ['pickupDate' => 'DESC']
        );

        $ordersData = array_map(function (Reservation $r) {
            return [
                'id' => $r->getId(),
                'dish' => $r->getDish()->getName(),
                'quantity' => $r->getQuantity(),
                'totalAmount' => $r->getTotalAmount(),
                'pickupDate' => $r->getPickupDate()->format('Y-m-d'),
                'pickupTime' => $r->getPickupDate()->format('H:i'),
                'status' => $r->getStatus(),
                'paymentType' => $r->getPaymentType(),
                'paymentStatus' => $r->getPaymentStatus(),
            ];
        }, $reservations);

        return new JsonResponse([
            'orders' => $ordersData
        ]);
    }

    #[Route('/api/cancel-order/{id}', name: 'cancel_order', methods: ['POST'])]
    public function cancelOrder(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $reservation = $em->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        if ($reservation->getClient() !== $user) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        if (strtolower($reservation->getStatus()) !== 'pending' || strtolower($reservation->getPaymentStatus()) !== 'unpaid') {
            return new JsonResponse(['error' => 'This order cannot be cancelled'], 400);
        }

        $reservation->setStatus('cancelled');
        $em->persist($reservation);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'order' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus(),
                'paymentStatus' => $reservation->getPaymentStatus(),
                'pickupDate' => $reservation->getPickupDate()->format('Y-m-d'),
            ]
        ]);
    }

    #[Route('/api/settings', name: 'api_settings')]
    public function index(SettingsRepository $repo): JsonResponse
    {
        $settings = $repo->find(1);
        return $this->json([
            'tax' => $settings ? $settings->getTax() : 1,
        ]);
    }

    #[Route('/api/chefs/{id}/reviews', name: 'api_add_review', methods: ['POST'])]
    public function addReview(int $id, Request $request, EntityManagerInterface $em, UserRepository $userRepo): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $chef = $userRepo->find($id);
        if (!$chef) {
            return $this->json(['error' => 'Chef not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $review = new Review();
        $review->setClient($user);
        $review->setChef($chef);
        $review->setRating($data['rating'] ?? 0);
        $review->setComment($data['comment'] ?? '');

        $em->persist($review);
        $em->flush();

        return $this->json([
            'message' => 'Review added successfully!',
            'review' => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'client' => $user->getName(),
                'chef' => $chef->getName(),
            ]
        ], 201);
    }


    #[Route('/api/chefs/{id}/reviews', name: 'api_get_reviews', methods: ['GET'])]
    public function getReviews(int $id, EntityManagerInterface $em): JsonResponse {
        $chef = $em->getRepository(User::class)->find($id);
        if (!$chef) {
            return $this->json(['error' => 'Chef not found'], 404);
        }

        $reviews = $em->getRepository(Review::class)->findBy(
            ['chef' => $chef],
            ['createdAt' => 'DESC']
        );

        $data = [];
        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'reviewer' => $review->getClient()->getName(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }



}