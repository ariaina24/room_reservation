<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserRoomController extends AbstractController
{
    #[Route('/user/rooms', name: 'user_room_list')]
   public function index(RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findAll();

        return $this->render('user/room/accueil.html.twig', [
            'rooms' => $rooms
        ]);
    }

    #[Route('/user/room/{id}', name: 'user_room_show')]
    public function show(int $id, RoomRepository $roomRepository, ReservationRepository $reservationRepository): Response
    {
        $room = $roomRepository->find($id);

        if (!$room) {
            throw $this->createNotFoundException('Salle introuvable.');
        }

        $reservations = $reservationRepository->findUpcomingByRoom($id);

        return $this->render('user/room/show.html.twig', [
            'room' => $room,
            'reservations' => $reservations,
        ]);
    }

}
