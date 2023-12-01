<?php

namespace App\Controller;

use App\Entity\Smartphone;
use App\Repository\SmartphoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class SmartphoneController extends AbstractController
{
    #[Route('/smartphones', name: 'smartphones', methods: ['GET'])]
    public function getSmartphones(SmartphoneRepository $smartphoneRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getSmartphones-" . $page . "-" . $limit;
        $jsonSmartphones = $cachePool->get($idCache, function (ItemInterface $item) use ($smartphoneRepository, $page, $limit, $serializer) {
           $item->tag("smartphonesCache");
            $item->expiresAfter(60);
           $smartphoneList = $smartphoneRepository->getAllWithPagination($page, $limit);
           return $serializer->serialize($smartphoneList, 'json');
        });

        return new JsonResponse($jsonSmartphones, Response::HTTP_OK, [], true);
    }

    #[Route('/smartphones/{id}', name: 'smartphone', methods: ['GET'])]
    public function getSmartphone(Smartphone $smartphone, SerializerInterface $serializer): JsonResponse
    {
        $jsonSmartphone = $serializer->serialize($smartphone, 'json');

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }
}
