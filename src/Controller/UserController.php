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
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/{id}/users', name: 'users', methods: ['GET'])]
    public function getUsers(Client $client, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findBy(['client' => $client]);
        $jsonUsers = $serializer->serialize($users, 'json');

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/users/{userId}', name: 'user', methods: ['GET'])]
    public function getUserDetail(Client $client, int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findBy(['client' => $client, 'id' => $userId]);
        $jsonSmartphone = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/users', name:"createUser", methods: ['POST'])]
    public function createUser(Client $client, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('user', ['id' => $client->getId(), 'userId' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
