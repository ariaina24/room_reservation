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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class UserReservationController extends AbstractController
{
    #[Route('/user/reservation/{id?}', name: 'user_reservation')]
    public function reservation(
        ?int $id,
        RoomRepository $roomRepository,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer // ✅ injection du service d’email
    ): Response {
        $rooms = $roomRepository->findAll();
        $selectedRoom = $id ? $roomRepository->find($id) : null;

        if ($request->isMethod('POST')) {
            $roomId = $request->request->get('room_id');
            $date = $request->request->get('date');
            $startTime = $request->request->get('start_time');
            $endTime = $request->request->get('end_time');

            $room = $roomRepository->find($roomId);
            $reservationDate = new \DateTime($date);
            $startDateTime = new \DateTime($startTime);
            $endDateTime = new \DateTime($endTime);

            $conflict = $em->getRepository(Reservation::class)->createQueryBuilder('r')
                ->andWhere('r.room = :room')
                ->andWhere('r.ReservationDate = :date')
                ->andWhere('(r.startTime < :endTime AND r.endTime > :startTime)')
                ->setParameter('room', $room)
                ->setParameter('date', $reservationDate)
                ->setParameter('startTime', $startDateTime)
                ->setParameter('endTime', $endDateTime)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($conflict) {
                $this->addFlash('error', 'La salle est déjà réservée à cet horaire.');
                return $this->redirectToRoute('user_reservation', ['id' => $roomId]);
            }

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUser($this->getUser());
            $reservation->setReservationDate($reservationDate);
            $reservation->setStartTime($startDateTime);
            $reservation->setEndTime($endDateTime);
            $reservation->setStatus('pending');

            $em->persist($reservation);
            $em->flush();

            $adminEmail = 'njivaariaina47@gmail.com';

            $email = (new Email())
                ->from('noreply@roomreservation.com')
                ->to($adminEmail)
                ->subject('Nouvelle réservation de salle')
                ->html("
                    <html>
                        <body style=\"margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;\">
                            <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                                <tr>
                                    <td align=\"center\" style=\"padding: 20px 0;\">
                                        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);\">
                                            <tr>
                                                <td style=\"background-color: #007bff; padding: 20px; color: white; text-align: center;\">
                                                    <h2>Nouvelle réservation de salle</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style=\"padding: 20px;\">
                                                    <p><strong>Utilisateur :</strong> {$this->getUser()->getEmail()}</p>
                                                    <p><strong>Salle :</strong> {$room->getName()}</p>
                                                    <p><strong>Date :</strong> {$reservationDate->format('d/m/Y')}</p>
                                                    <p><strong>De :</strong> {$startDateTime->format('H:i')}</p>
                                                    <p><strong>À :</strong> {$endDateTime->format('H:i')}</p>
                                                    <p><strong>Status :</strong> <span style=\"color: #ff9800;\">En attente de validation</span></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style=\"padding: 20px; text-align: center; background-color: #f0f0f0; font-size: 12px; color: #777;\">
                                                    RoomReservation - Merci de ne pas répondre à cet email.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                    </html>
                ");

            $mailer->send($email);

            $this->addFlash('success', 'Réservation effectuée avec succès ! Un email a été envoyé à l\'administrateur.');
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
