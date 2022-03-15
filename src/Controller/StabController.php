<?php

namespace App\Controller;

use App\Service\StabService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\When;

// StabController is only registered in the "dev" environment

#[When(env: 'dev')]
#[Route('/admin', name: 'admin_')]
class StabController extends AbstractController
{
    private StabService $stabService;

    public function __construct(
        StabService $stabService
    )
    {
        $this->stabService = $stabService;
    }

    #[Route('/stab', name: 'show_stab')]
    public function showStab(Request $request): Response
    {
        $numberOfIterations = $request->query->get('number') ?? 0;
        $this->stabService->toStabDb($numberOfIterations);
        $errors = $this->stabService->getErrors() ?? false;
        return $this->render('admin/stab.html.twig', [
            'errors' => $errors,
            'numberOfIterations' => $numberOfIterations
        ]);
    }
}