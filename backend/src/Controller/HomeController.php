<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
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
