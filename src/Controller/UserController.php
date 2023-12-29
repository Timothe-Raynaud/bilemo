<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerialize;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

#[Route('/api')]
class UserController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des users rattaché à un client.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne l'ensemble des users rattaché à un client",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
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
     * @OA\Tag(name="Users")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $client = $this->getUser();

        if (!($client instanceof Client)) {
            return new JsonResponse('Vous n\'avez pas accés à ce endpoint', Response::HTTP_BAD_REQUEST, [], true);
        }

        $idCache = "getUsers-" . $client->getId() . "-" . $page . "-" . $limit;

        $jsonUsers = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $client, $limit, $serializer) {
            $item->tag("usersCache");
            $item->expiresAfter(60);
            $smartphoneList = $userRepository->getAllByClientWithPagination($client, $page, $limit);

            $context = SerializationContext::create()->setGroups(['getUsers']);
            return $serializer->serialize($smartphoneList, 'json', $context);
        });

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer les détails d'un utilisateur.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne les détails d'un utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     *
     * @OA\Tag(name="Users")
     *
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/users/{id}', name: 'user', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializer): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()) {
            return new JsonResponse('Vous n\'avez pas droit d\'accès à cet utilisateur.', Response::HTTP_UNAUTHORIZED, [], true);
        }

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonSmartphone = $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur.
     *
     * @OA\Response(
     *     response=204,
     *     description="Supprime un utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     *
     * @OA\Tag(name="Users")
     *
     * @param User $user
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()) {
            return new JsonResponse('Vous n\'avez pas droit d\'accès à cet utilisateur.', Response::HTTP_UNAUTHORIZED, [], true);
        }

        $cachePool->invalidateTags(["usersCache"]);
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet d'ajouter un utilisateur'.
     *
     * @OA\Response(
     *     response=201,
     *     description="Ajoute un utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     *
     * @OA\Tag(name="Users")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cachePool
     * @param SymfonySerialize $serializerSymony
     * @return JsonResponse
     */
    #[Route('/users', name: "createUser", methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool, SymfonySerialize $serializerSymony): JsonResponse
    {
        $client = $this->getUser();

        if (!($client instanceof Client)) {
            return new JsonResponse('Vous n\'avez pas accés à ce endpoint', Response::HTTP_UNAUTHORIZED, [], true);
        }

        $user = $serializerSymony->deserialize($request->getContent(), User::class, 'json', ['groups' => 'addUser']);
        $user->setClient($client);

        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $cachePool->invalidateTags(["usersCache"]);
        $em->persist($user);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('user', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
