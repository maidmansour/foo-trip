<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DestinationRepository;
use App\Entity\Destination;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DestinationRepository $repository): Response
    {
        $destinations = $repository->findAll();

        return $this->render('home/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }

    #[Route('/detail/{id}', name: 'home.detail')]
    public function detail(Destination $destination): Response
    {
        
        return $this->render('home/detail.html.twig', [
            'destination' => $destination,
        ]);
    }
}
