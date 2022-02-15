<?php
namespace App\Controller;

//use App\Entity\PostService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_", methods={"GET"})
 */
class UserController extends AbstractController
{
    /**
     * @Route("/cabinet", name="_show_cabinet", methods={"GET"})
     */
    public function showCabinet(Request $request, SessionInterface $sessionInterface): Response
    {
        $pageTitle = 'Кабинет - Просто Блог';
        $pageDescription = 'Кабинет - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        } else {
            $sessionUserId = (int) $sessionInterface->get('user_id');
        }
        $isSuperuser = true;
        return $this->render('blog_base.html.twig', [
            'title' => $pageTitle,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'pageDescription' => $pageDescription,
            'year' => date('Y', time())
        ]);
    }
    /**
     * @Route("/login", name="_show_login", methods={"GET"})
     */
    public function showLogin(Request $request, SessionInterface $sessionInterface): Response
    {
        $pageTitle = 'Войти - Просто Блог';
        $pageDescription = 'Войти в аккаунт - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        } else {
            $sessionUserId = (int) $sessionInterface->get('user_id');
        }
        $isSuperuser = true;
        return $this->render('blog_base.html.twig', [
            'title' => $pageTitle,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'pageDescription' => $pageDescription,
            'year' => date('Y', time())
        ]);
    }
        /**
     * @Route("/reg", name="_show_reg", methods={"GET"})
     */
    public function showReg(Request $request, SessionInterface $sessionInterface): Response
    {
        $pageTitle = 'Регистрация - Просто Блог';
        $pageDescription = 'Создать аккаунт - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        } else {
            $sessionUserId = (int) $sessionInterface->get('user_id');
        }
        $isSuperuser = true;
        return $this->render('blog_base.html.twig', [
            'title' => $pageTitle,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'pageDescription' => $pageDescription,
            'year' => date('Y', time())
        ]);
    }
    private function isUser(Request $request) {
        $request->cookies->get('user_id');
    }
}