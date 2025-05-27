<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez vous connecter.');

            return $this->redirectToRoute('login');
        }

        return $this->render('authentification/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
