<?php

namespace App\Controller;

use App\Service\StabService;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class StabController extends AbstractController
{
    private StabService $stabService;
    private KernelInterface $kernel;

    public function __construct(
        StabService $stabService,
        KernelInterface $kernel
    ) {
        $this->stabService = $stabService;
        $this->kernel = $kernel;
    }

    #[Route('/stab', name: 'show_stab')]
    public function showStab(Request $request): Response
    {
        $env = $this->kernel->getEnvironment();
        if ($env !== 'dev') {
            return $this->render('blog_message.html.twig', [
                'description' => "В $env-e Stab не работает"
            ]);
        }
        $numberOfIterations = $request->query->get('number') ?? 0;
        $this->stabService->toStabDb($numberOfIterations);
        $errors = $this->stabService->getErrors() ?? false;
        return $this->render('admin/stab.html.twig', [
            'errors'             => $errors,
            'numberOfIterations' => $numberOfIterations
        ]);
    }
}