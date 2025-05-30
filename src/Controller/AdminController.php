<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/accueil.html.twig');
    }

    #[Route('/admin/reservations', name: 'admin_reservations')]
    public function reservation(ReservationRepository $reservationRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $status = $request->query->get('status');

        if ($status && in_array($status, ['pending', 'acceptée', 'refusée'])) {
            $reservations = $reservationRepository->findBy(
                ['status' => $status],
                ['createdAt' => 'DESC']
            );
        } else {
            // Pas de filtre ou filtre invalide, on prend tout
            $reservations = $reservationRepository->findBy([], ['createdAt' => 'ASC']);
        }

        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/admin/reservation/{id}/accept', name: 'admin_reservation_accept')]
    public function accept(Reservation $reservation, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $reservation->setStatus('acceptée');
        $em->flush();

        $user = $reservation->getUser();
        $room = $reservation->getRoom();

       $email = (new TemplatedEmail())
            ->from('noreply@roomreservation.com')
            ->to($user->getEmail())
            ->subject('Réservation ' . $reservation->getStatus())
            ->htmlTemplate('emails/reservation_accepted.html.twig')
            ->context([
                'user' => $user,
                'room' => $reservation->getRoom(),
                'reservation' => $reservation,
            ]);

        $mailer->send($email);

        $this->addFlash('success', 'Réservation acceptée.');
        return $this->redirectToRoute('admin_reservations');
    }

    #[Route('/admin/reservation/{id}/refuse', name: 'admin_reservation_refuse')]
    public function refuse(Reservation $reservation, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $reservation->setStatus('refusée');
        $em->flush();

        $user = $reservation->getUser();
        $room = $reservation->getRoom();

       $email = (new TemplatedEmail())
            ->from('noreply@roomreservation.com')
            ->to($user->getEmail())
            ->subject('Réservation ' . $reservation->getStatus())
            ->htmlTemplate('emails/reservation_refused.html.twig')
            ->context([
                'user' => $user,
                'room' => $reservation->getRoom(),
                'reservation' => $reservation,
            ]);

        $mailer->send($email);

        $this->addFlash('warning', 'Réservation refusée.');
        return $this->redirectToRoute('admin_reservations');
    }

    #[Route('/admin/reservations/calendar', name: 'admin_reservations_calendar')]
    public function reservationsCalendar(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/reservation/calendar.html.twig');
    }

    #[Route('/admin/reservations/calendar/data', name: 'admin_reservations_calendar_data')]
    public function reservationsCalendarData(ReservationRepository $reservationRepository): JsonResponse
    {
        $reservations = $reservationRepository->findAll();

        $events = [];
        foreach ($reservations as $reservation) {
            $events[] = [
                'id' => $reservation->getId(),
                'title' => $reservation->getRoom()->getName() . ' - ' . $reservation->getUser()->getEmail(),
                'start' => $reservation->getReservationDate()->format('Y-m-d') . 'T' . $reservation->getStartTime()->format('H:i:s'),
                'end' => $reservation->getReservationDate()->format('Y-m-d') . 'T' . $reservation->getEndTime()->format('H:i:s'),
                'color' => match($reservation->getStatus()) {
                    'acceptée' => 'green',
                    'refusée' => 'red',
                    default => 'orange',
                },
            ];
        }

        return new JsonResponse($events);
    }
}
