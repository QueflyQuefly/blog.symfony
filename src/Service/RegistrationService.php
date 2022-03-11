<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\SubscriptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\EmailVerifier;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class RegistrationService
{
    private UserRepository $userRepository;
    private SubscriptionsRepository $subscriptionsRepository;
    private EmailVerifier $emailVerifier;
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EmailVerifier $emailVerifier,
        UserPasswordHasherInterface $userPasswordHasher, 
        UserRepository $userRepository,
        SubscriptionsRepository $subscriptionsRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->emailVerifier = $emailVerifier;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return User Returns an User object
     */
    public function register(string $email, string $fio, string $password, array $rights, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $user = new User();
        $user->setEmail($email);
        $user->setFio($fio);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setDateTime($dateTime);
        $user->setRoles($rights);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
        (new TemplatedEmail())
            ->from(new Address('prostoblog.local@gmail.com', 'Prosto Blog'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        return $user;
    }

    /**
     * @return User Returns an User object
     */
    public function registerWithoutEmailVerification(string $email, string $fio, string $password, array $rights, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $user = new User();
        $user->setEmail($email);
        $user->setFio($fio);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setDateTime($dateTime);
        $user->setRoles($rights);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}