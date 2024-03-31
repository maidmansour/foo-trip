<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Destination;
use App\Repository\DestinationRepository;
use  App\Form\DestinationType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED')]
class AdminController extends AbstractController
{
    
    #[Route('/admin', name: 'admin.index')]
    public function index(DestinationRepository $repository): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $destinations = $repository->findAll();

        return $this->render('admin/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }

    #[Route('/admin/create', name: 'admin.create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $destination->setCreatedAt(new \DateTimeImmutable);
            $destination->setUpdatedAt(new \DateTimeImmutable);
            $em->persist($destination);
            $em->flush();
            $this->addFlash('success', 'La destination a bien été crée');
            return $this->redirectToRoute('admin.index');
        }
        return $this->render('admin/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/admin/edit/{id<\d+>}', name: 'admin.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, int $id)
    {
            $destination = $em->getRepository(Destination::class)->find($id);

            if (!$destination) {
                $this->addFlash('warning', 'Destination not found');
                return $this->redirectToRoute('admin.index');
            }
            

            $form = $this->createForm(DestinationType::class, $destination);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $destination->setUpdatedAt(new \DateTimeImmutable);
                $em->persist($destination);
                $em->flush();
                $this->addFlash('success', 'La destination a bien été mofifiée');
                return $this->redirectToRoute('admin.index');
            }
            return $this->render('admin/edit.html.twig', [
                'form' => $form,
                'destination' => $destination
            ]);
        
        
    }

    #[Route('/admin/delete/{id<\d+>}', name: 'admin.delete', methods: ['DELETE'])]
    public function remove(EntityManagerInterface $em, Destination $destination)
    {
        $em->remove($destination);
        $em->flush();
        $this->addFlash('success', 'La destination a bien été supprimée');
        
        return $this->redirectToRoute('admin.index');
    }
}
