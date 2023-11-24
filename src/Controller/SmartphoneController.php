<?php

namespace App\Controller;

use App\Entity\Smartphone;
use App\Repository\SmartphoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class SmartphoneController extends AbstractController
{
    #[Route('/smartphones', name: 'smartphones', methods: ['GET'])]
    public function getSmartphones(SmartphoneRepository $smartphoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $smartphones = $smartphoneRepository->findAll();
        $jsonSmartphones = $serializer->serialize($smartphones, 'json');

        return new JsonResponse($jsonSmartphones, Response::HTTP_OK, [], true);
    }

    #[Route('/smartphones/{id}', name: 'smartphone', methods: ['GET'])]
    public function getSmartphone(Smartphone $smartphone, SerializerInterface $serializer): JsonResponse
    {
        $jsonSmartphone = $serializer->serialize($smartphone, 'json');

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }
}
