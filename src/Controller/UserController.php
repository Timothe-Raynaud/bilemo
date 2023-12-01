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

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository, SerializerInterface $serializer, Request $request,  TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $client = $this->getUser();

        if (!($client instanceof Client)){
            return new JsonResponse('Vous n\'avez pas accés à ce endpoint', Response::HTTP_BAD_REQUEST, [], true);
        }

        $idCache = "getUsers-". $client->getId() . "-" . $page . "-" . $limit;

        $jsonUsers = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $client, $limit, $serializer) {
            $item->tag("usersCache");
            $item->expiresAfter(60);
            $smartphoneList = $userRepository->getAllByClientWithPagination($client, $page, $limit);

            $context = SerializationContext::create()->setGroups(['getUsers']);
            return $serializer->serialize($smartphoneList, 'json', $context);
        });

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'user', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializer): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()){
            return new JsonResponse('Vous n\'avez pas droit d\'accès à cet utilisateur.', Response::HTTP_BAD_REQUEST, [], true);
        }

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonSmartphone = $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()){
            return new JsonResponse('Vous n\'avez pas droit d\'accès à cet utilisateur.', Response::HTTP_BAD_REQUEST, [], true);
        }

        $cachePool->invalidateTags(["usersCache"]);
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/users', name:"createUser", methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool, SymfonySerialize $serializerSymony): JsonResponse
    {
        $client = $this->getUser();
        $user = $serializerSymony->deserialize($request->getContent(), User::class, 'json', ['groups' => 'addUser']);
        $user->setClient($client);

        $errors = $validator->validate($user);
        if ($errors->count() > 0){
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
