<?php
namespace App\Controller;

use App\User\UserCreator;
use App\Exception\UserCreatorException;
use App\Interfaces\IUserRepo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;

class UserController extends AbstractController
{

	/**
	 * Create a new user.
	 *
	 * @Route("/users", name="createUser", methods={"POST"})
	 * @SWG\Tag(name="1. user")
	 *
	 * @SWG\Parameter(name="body", in="body", required=true,
	 *   @SWG\Schema(required={"name", "surname"}, @Model(type=User::class, groups={"CREATE"}))
	 * )
	 *	
	 * @SWG\Response(response=200, description="", @Model(type=User::class))
	 * 
	 * @param UserCreator $createUserService
	 * @return JsonResponse
	 */
	public function createUser(UserCreator $createUserService): JsonResponse
	{
		try {
			$createdUser = $createUserService->handle();
			return $this->json($createdUser, Response::HTTP_CREATED);
		} catch (UserCreatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}

	/**
	 * View a user.
	 * 
	 * @Route("/users/{id}", name="getUserById", methods={"GET"})
	 * @SWG\Tag(name="1. user")
	 * 
	 * @SWG\Response(response=200, description="", @Model(type=User::class))
	 * 
	 * @param IUserRepo $repo
	 * @param int $id
	 * @return JsonResponse|Response
	 */
	public function getUserById(IUserRepo $repo, int $id)
	{
		$user = $repo->getById($id);
		if ($user)
			return $this->json($user, Response::HTTP_OK);
		return new Response(null, Response::HTTP_NOT_FOUND);
	}

	/**
	 * View all user.
	 * 
	 * @Route("/users", name="getUsers", methods={"GET"})
	 * @SWG\Tag(name="1. user")
	 * 
	 * @SWG\Response(response=200, description="",@SWG\Schema(type="array", @SWG\Items(@Model(type=User::class))))
	 * 
	 * @param IUserRepo $repo
	 * @return JsonResponse
	 */
	public function getUsers(IUserRepo $repo): JsonResponse
	{
		$users = $repo->getAll();
		return $this->json($users, Response::HTTP_OK);
	}
}
