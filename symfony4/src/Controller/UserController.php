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

class UserController extends AbstractController
{

	/**
	 * Create a new user.
	 *
	 * Each user is assigned 100$ or 10000 cents as a starting balance to make orders later on.      
	 * - "name" : accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-).
	 * - "surname" : accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-).
	 *
	 * @Route("/users", name="createUser", methods={"POST"})
	 * @SWG\Tag(name="user")
	 *
	 * @SWG\Parameter(
	 *   name="body",
	 *   in="body",
	 *   required=true,
	 *   @SWG\Schema(
	 *    required={"name", "surname"},
	 *    @SWG\Property(property="name", type="string", example="John", description="accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-)."),
	 *    @SWG\Property(property="surname", type="string", example="Doe", description="accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-).")
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example=1),
	 *    @SWG\Property(property="name", type="string", example="John"),
	 *    @SWG\Property(property="surname", type="string", example="Doe"),
	 *    @SWG\Property(property="balance", type="integer", example=10000),
	 *   )
	 * )       
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
	 * @SWG\Tag(name="user")
	 * 
	 * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example="1"),
	 *    @SWG\Property(property="name", type="string", example="John"),
	 *    @SWG\Property(property="surname", type="string", example="Doe"),
	 *    @SWG\Property(property="balance", type="integer", example=10000),
	 *   )
	 * )       
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
	 * @SWG\Tag(name="user")
	 * 
	 * @SWG\Response(
	 *   response=200, description="",
	 *     @SWG\Schema(
	 *       type="array",
	 *       @SWG\Items(
	 *         type="object",
	 *         example = {
	 *           {"id": "1", "name": "John", "surname": "Doe", "balance": 10000},
	 *           {"id": "2", "name": "Alice", "surname": "Doe  ", "balance": 10000}
	 *         },
	 *         @SWG\Property(property="id", type="integer", example=1),
	 *         @SWG\Property(property="name", type="string", example="John"),
	 *         @SWG\Property(property="surname", type="string", example="Doe"),
	 *         @SWG\Property(property="balance", type="integer", example=10000),
	 *       )
	 *    )
	 * )   
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
