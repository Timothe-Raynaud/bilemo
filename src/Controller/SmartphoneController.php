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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api')]
class SmartphoneController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des smartphones.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des smartphones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Smartphone::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Smartphone")
     *
     * @param SmartphoneRepository $smartphoneRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Cette méthode permet de récupérer les details d'un smartphone.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne les details d'un smartphone",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Smartphone::class))
     *     )
     * )
     *
     * @OA\Tag(name="Smartphone")
     *
     * @param Smartphone $smartphone
     * @param SerializerInterface $serialize
     * @return JsonResponse
     */
    #[Route('/smartphones/{id}', name: 'smartphone', methods: ['GET'])]
    public function getSmartphone(Smartphone $smartphone, SerializerInterface $serializer): JsonResponse
    {
        $jsonSmartphone = $serializer->serialize($smartphone, 'json');

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }
}
