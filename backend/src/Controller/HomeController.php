<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // Match any route except those starting with /api/
    #[Route('/{vueRouting}', name: 'app_home_spa', requirements: ['vueRouting' => '^(?!api/).*'], priority: -100)]
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $indexPath = $this->getParameter('kernel.project_dir') . '/public/index.html';
        if (file_exists($indexPath)) {
            return new Response(file_get_contents($indexPath));
        }

        return new Response('Frontend index.html not found in public folder.', 404);
    }
}
