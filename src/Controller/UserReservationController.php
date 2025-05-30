<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserReservationController extends AbstractController
{
    #[Route('/user/reservation/{id?}', name: 'user_reservation')]
    public function reservation(?int $id, RoomRepository $roomRepository, Request $request, EntityManagerInterface $em): Response
    {
        $rooms = $roomRepository->findAll();
        $selectedRoom = $id ? $roomRepository->find($id) : null;

        if ($request->isMethod('POST')) {
            $roomId = $request->request->get('room_id');
            $date = $request->request->get('date');
            $startTime = $request->request->get('start_time');
            $endTime = $request->request->get('end_time');

            $room = $roomRepository->find($roomId);

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUser($this->getUser());
            $reservation->setReservationDate(new \DateTime($date));
            $reservation->setStartTime(new \DateTime($startTime));
            $reservation->setEndTime(new \DateTime($endTime));
            $reservation->setStatus('pending');

            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation effectuée avec succès !');
            return $this->redirectToRoute('user_room_list');
        }

        return $this->render('user/reservation/index.html.twig', [
            'rooms' => $rooms,
            'selectedRoom' => $selectedRoom
        ]);
    }

    #[Route('/user/mes-reservations', name: 'user_my_reservations')]
    public function myReservation(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $today = new \DateTime('today');

        $reservations = $reservationRepository->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.ReservationDate >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->orderBy('r.ReservationDate', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('user/reservation/my_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/user/reservation/{id}/cancel', name: 'user_reservation_cancel')]
    public function cancelReservation(int $id, ReservationRepository $repo, EntityManagerInterface $em, Request $request): Response
    {
        $reservation = $repo->find($id);

        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("Réservation non trouvée.");
        }

        $em->remove($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');

        return $this->redirectToRoute('user_my_reservations');
    }

    #[Route('/user/mon-calendrier', name: 'user_my_calendar')]
    public function calendar(): Response
    {
        return $this->render('user/reservation/my_calendar.html.twig');
    }


    #[Route('/user/reservations-json', name: 'user_reservations_json')]
    public function userReservationsJson(ReservationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $repo->findBy(['user' => $user]);

        $events = [];

        foreach ($reservations as $reservation) {
            $events[] = [
                'title' => $reservation->getRoom()->getName(),
                'start' => $reservation->getReservationDate()->format('Y-m-d') . 'T' . $reservation->getStartTime()->format('H:i:s'),
                'end' => $reservation->getReservationDate()->format('Y-m-d') . 'T' . $reservation->getEndTime()->format('H:i:s'),
                'extendedProps' => [
                    'status' => $reservation->getStatus(),
                    'capacity' => $reservation->getRoom()->getCapacity(),
                ],
            ];
        }

        return $this->json($events);
    }

}
