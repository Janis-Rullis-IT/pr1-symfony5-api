<?php
namespace App\Controller;

use \App\Exception\OrderValidatorException;
use App\Interfaces\IOrderRepo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use \App\Repository\v2\OrderRepository;

class OrderController extends AbstractController
{

	/**
	 * View user's order.
	 * 
	 * @Route("/users/{id_user}/orders/{id}", name="viewOrder", methods={"GET"})
	 * 
	 * @param IOrderRepo $repo
	 * @param int $id_user
	 * @param int $id
	 * @return Response
	 */
	public function getOrderById(OrderRepository $repo, int $id_user, int $id)
	{
		try {
			$resp = $repo->mustFindUsersOrderWithProducts($id_user, $id);
			return $this->json($resp, Response::HTTP_OK);
		} catch (OrderValidatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}

	/**
	 *  View user's all orders.
	 * 
	 * @Route("/users/{id_user}/orders", name="viewOrders", methods={"GET"})
	 * @SWG\Tag(name="TODO replace order")
	 * @SWG\Response(
	 *   response=200, description="",
	 *     @SWG\Schema(
	 *       type="array",
	 *       @SWG\Items(
	 *         type="object",
	 *    @SWG\Property(property="shipToAddress", type="object", example={ "name": "John", "surname": "Doe", "street": "Palm Street 255", "state" : "NY", "zip" : "12315", "country": "US", "phone": "917-568-2970" }),
	 *    @SWG\Property(property="lineItems", type="object", example={{ "id": 1, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "just do it", "sku": "100-abc-999", "cost": 1000, "totalCost": 1000 }, { "id": 2, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "mo mamba", "sku": "100-abc-100", "cost": 1000, "totalCost": 1000 }}),
	 *    @SWG\Property(property="info", type="object", example={ "id": 3, "ownerId": 1, "productionCost": 2000, "shippingCost": 2000, "expressShipping": true, "totalCost": 4000 }),
	 *       )
	 *    )
	 * )   
	 * @param IOrderRepo $repo
	 * @param int $id_user
	 * @return JsonResponse|Response
	 */
	public function getOrders(IOrderRepo $repo, int $id_user)
	{
		$orders = $repo->getAll($id_user);
		if ($orders !== null)
			return $this->json($orders, Response::HTTP_OK);
		return new Response(null, Response::HTTP_NOT_FOUND);
	}
}
