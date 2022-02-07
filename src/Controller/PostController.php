<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class PostController
{
    public function showPosts(): Response
    {
        $startTime = microtime(true);
        $number = (10);
        $pageTitle = 'Просто Блог';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        require '../templates/head.layout.php';
        require '../templates/menu.layout.php';
        require '../templates/description.layout.php';
        require '../templates/endbody.layout.php';
        return new Response(
            
        );
    }
}