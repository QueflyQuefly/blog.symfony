<?php
namespace App\Controller;

/* use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry; */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private $user, $isUser, $isAdmin;

    public function __construct()
    {
        $this->isUser = $this->isGranted('ROLE_USER');
        $this->isAdmin = $this->isGranted('ROLE_ADMIN');
        
        // /** @var \App\Entity\User $user */
        // $this->user = $this->getUser();
        // if ($this->user)
        // {
        //     $this->isUser = true;
        //     if ($this->user->getRoles()[0] == 'ROLE_ADMIN')
        //     {
        //         $this->isAdmin = true;
        //     }
        // }
    }

    #[Route('/cabinet', name: 'show_cabinet', methods: ['GET'])]
    public function showCabinet(Request $request): Response
    {
        if (!$this->isUser)
        {
            return $this->redirectToRoute('user_login');
        }
        $pageDescription = 'Кабинет - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        }

        return $this->render('blog_message.html.twig', [
            'description' => $pageDescription
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout()
    {
        // controller can be blank: it will never be called!
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}