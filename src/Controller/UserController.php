<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/{id}/users', name: 'users', methods: ['GET'])]
    public function getUsers(Client $client, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findBy(['client' => $client]);
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/users/{user_id}', name: 'user', methods: ['GET'])]
    #[Entity('user', options: ['id' => 'user_id'])]
    public function getUserDetail(Client $client, User $user, SerializerInterface $serializer): JsonResponse
    {
        // TODO Verify if client is connected
        $jsonSmartphone = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/users/{user_id}', name: 'deleteUser', methods: ['DELETE'])]
    #[Entity('user', options: ['id' => 'user_id'])]
    public function deleteUser(Client $client, User $user, EntityManagerInterface $em): JsonResponse
    {
        // TODO Verify if client is connected
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/users', name:"createUser", methods: ['POST'])]
    public function createUser(Client $client, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', ['groups' => 'addUser']);
        $user->setClient($client);

        $errors = $validator->validate($user);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('user', ['id' => $client->getId(), 'user_id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
