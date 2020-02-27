<?php

namespace App\Controller;

use App\User\UserCreator;
use App\Exception\UserCreatorException;
use App\Interfaces\IUserRepo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="createUser", methods={"POST"})
     * @param UserCreator $createUserService
     * @return JsonResponse
     */
    public function createUser(UserCreator $createUserService): JsonResponse
    {
        try {
            $createdUser = $createUserService->handle();
            return $this->json($createdUser, Response::HTTP_CREATED);
        } catch (UserCreatorException $e){
            return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/users/{id}", name="getUser", methods={"GET"})
     * @param IUserRepo $repo
     * @param int $id
     * @return JsonResponse|Response
     */

    public function getUserById(IUserRepo $repo, int $id)
    {      
        $user = $repo->getById($id);
        if ($user)
            return $this->json($user, Response::HTTP_OK);
        return new Response(null,Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/users", name="getUsers", methods={"GET"})
     * @param IUserRepo $repo
     * @return JsonResponse
     */
    public function getUsers(IUserRepo $repo): JsonResponse
    {
      die('UserController::getUsers');
        $users =  $repo->getAll();
        return $this->json($users, Response::HTTP_OK);
    }
}
