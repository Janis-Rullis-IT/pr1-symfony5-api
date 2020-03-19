<?php
namespace App\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use \App\v2\OrderCreator;
use \App\Exception\UidValidatorException;
use \App\Exception\ProductIdValidatorException;

class OrderController extends AbstractController
{

	/**
	 * #40 Set order's shipping.
	 * 
	 * @Route("/users/v2/{customerId}/order/shipping", methods={"POST"})   
	 * @SWG\Tag(name="v2:shipping")
	 * 
	 * @SWG\Parameter(
	 *   name="body",
	 *   in="body",
	 *   required=true,
	 *   @SWG\Schema(
	 *    required={"name", "surname", "street", "country", "phone", "is_express"},
	 *    @SWG\Property(property="name", type="string", example="John"),
	 *    @SWG\Property(property="surname", type="string", example="Doe"),
	 *    @SWG\Property(property="street", type="string", example="Palm street 25-7"),
	 *    @SWG\Property(property="state", type="string", example="California"),
	 *    @SWG\Property(property="zip", type="string", example="60744"),
	 *    @SWG\Property(property="country", type="string", example="US"),
	 *    @SWG\Property(property="phone", type="string", example="+1 123 123 123"),
	 *    @SWG\Property(property="is_express", type="boolean", example=true)
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=200, description="Saved.",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="is_domestic", type="string", example="y"),
	 *    @SWG\Property(property="is_express", type="string", example="y"),
	 *    @SWG\Property(property="shipping_cost", type="integer", example=100),
	 *    @SWG\Property(property="product_cost", type="integer", example=100),
	 *    @SWG\Property(property="total_cost", type="integer", example=200),
	 *    @SWG\Property(property="name", type="string", example="John"),
	 *    @SWG\Property(property="surname", type="string", example="Doe"),
	 *    @SWG\Property(property="street", type="string", example="Palm street 25-7"),
	 *    @SWG\Property(property="state", type="string", example="California"),
	 *    @SWG\Property(property="zip", type="string", example="60744"),
	 *    @SWG\Property(property="country", type="string", example="US"),
	 *    @SWG\Property(property="phone", type="string", example="+1 123 123 123")
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=404, description="Not found.",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="string", example="invalid user"),
	 *   )
	 * )
	 * 
	 * @param \App\Controller\v2\OrderCreator $orderCreator
	 * 
	 * @param Request $request
	 * @param OrderCreator $orderCreator
	 * @param type $customerId
	 * @return JsonResponse
	 */
	public function setShipping(Request $request, OrderCreator $orderCreator, int $customerId): JsonResponse
	{
		try {
			// #40 POST handling https://stackoverflow.com/a/54944381 https://github.com/symfony/http-foundation/blob/master/Request.php#L715 .
			// #40 JSON handling https://stackoverflow.com/a/57281311 .
			$item = $orderCreator->handle($customerId, json_decode($request->getContent(), true));
			$resp = ["is_domestic" => $item->getIsDomestic(), "is_express" => $item->getIsExpress(), "shipping_cost" => $item->getShippingCost(),
				"product_cost" => $item->getProductCost(), "total_cost" => $item->getTotalCost(), "name" => $item->getName(),
				"surname" => $item->getSurname(), "street" => $item->getStreet(), "country" => $item->getCountry(),
				"phone" => $item->getPhone(), "state" => $item->getState(), "zip" => $item->getZip()];
			return $this->json($resp, Response::HTTP_OK);
		} catch (UidValidatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}
}
