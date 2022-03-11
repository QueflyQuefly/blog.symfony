<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RegistrationService;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;


class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private RegistrationService $registrationService;

    public function __construct(
        EmailVerifier $emailVerifier,
        RegistrationService $registrationService
    )
    {
        $this->emailVerifier = $emailVerifier;
        $this->registrationService = $registrationService;
    }

    #[Route('/user/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $fio = $form->get('fio')->getData();
            $password = $form->get('plainPassword')->getData();
            $rights = ['ROLE_USER'];
            if ($form->get('addAdmin')->getData())
            {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $rights = ['ROLE_ADMIN'];
            }
            $this->registrationService->register($email, $fio, $password, $rights);
            return $this->redirectToRoute('user_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Произошла ошибка при проверке e-mail');

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Ваш e-mail верифицирован. Войдите');
        return $this->redirectToRoute('user_login');
    }
}
