<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ApiDestinationController extends AbstractController
{
   
    #[Route('/api/destination', name: 'app_api_destination', methods : ['GET'])]
    public function index(DestinationRepository $repository): JsonResponse
    {
        $destinations = $repository->findAll();

        return $this->json($destinations, 200);
    }

    #[Route('/api/destination', name: 'app_api_create_destination', methods : ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $destination = new Destination();
        $destination->setName($data['name'] ?? '');
        $destination->setDescription($data['description'] ?? '');
        $destination->setPrice($data['price'] ?? 0);
        $destination->setDuration($data['duration'] ?? 0);

        $errors = $validator->validate($destination);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($errorMessages, JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gérer l'image en base64
        if (isset($data['image'])) {
            [$type, $data] = explode(';', $data['image']);
            [$format, $data] = explode(',', $data);
            $imageData = base64_decode($data);
            $extension = str_replace('data:image/', '', $type);
            $imageName = uniqid() . '.' . $extension;
            $imagePath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'destinations' . DIRECTORY_SEPARATOR . $imageName;
            file_put_contents($imagePath, $imageData);
            $destination->setImage($imageName);
        }

        $destination->setCreatedAt(new \DateTimeImmutable());
        $destination->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($destination);
        $em->flush();

        return $this->json($destination, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/destination', name: 'app_api_update_destination', methods : ['PUT'])]
    public function update(Request $request, DestinationRepository $repository, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;

        if (!$id) {
            return $this->json(['error' => 'ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $destination = $repository->find($id);

        if (!$destination) {
            return $this->json(['error' => 'Destination not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $destination->setName($data['name'] ?? '');
        $destination->setDescription($data['description'] ?? '');
        $destination->setPrice($data['price'] ?? 0);
        $destination->setDuration($data['duration'] ?? 0);

        $errors = $validator->validate($destination);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($errorMessages, JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gérer l'image en base64
        if (isset($data['image'])) {
            [$type, $data] = explode(';', $data['image']);
            [$format, $data] = explode(',', $data);
            $imageData = base64_decode($data);
            $extension = str_replace('data:image/', '', $type);
            $imageName = uniqid() . '.' . $extension;
            $imagePath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'destinations' . DIRECTORY_SEPARATOR . $imageName;
            file_put_contents($imagePath, $imageData);
            $destination->setImage($imageName);
        }

        $destination->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json($destination,JsonResponse::HTTP_OK);
    }

    #[Route('/api/destination', name: 'app_api_delete_destination', methods : ['DELETE'])]
    public function delete(Request $request, DestinationRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;

        if (!$id) {
            return $this->json(['error' => 'ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $destination = $repository->find($id);

        if (!$destination) {
            return $this->json(['error' => 'Destination not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Supprimer l'image
        if ($destination->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'destinations' . DIRECTORY_SEPARATOR . $destination->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $em->remove($destination);
        $em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
