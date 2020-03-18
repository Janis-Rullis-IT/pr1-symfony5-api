<?php
namespace App\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use \App\v2\OrderProductCreator;

class OrderProductController extends AbstractController
{

	/**
	 * #40 #38 Add a product to customer's cart (draft order).
	 * 
	 * @Route("/users/{customerId}/v2/cart/{productId}", name="createProduct", methods={"POST"})   
	 * @SWG\Tag(name="cart")
	 * @SWG\Response(
	 *   response=200, description=""
	 * )
	 * 
	 * @param OrderProductCreator $orderProductCreator
	 * @param int $customerId
	 * @param int $productId
	 * @return Response
	 */
	public function addProductToCart(OrderProductCreator $orderProductCreator, int $customerId, int $productId)
	{
		try {
			$orderProduct = $orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
			return $this->json($orderProduct, Response::HTTP_CREATED);
		} catch (UidValidatorException $e) {
			return new Response(null, Response::HTTP_NOT_FOUND);
		} catch (OrderCreatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}
}
