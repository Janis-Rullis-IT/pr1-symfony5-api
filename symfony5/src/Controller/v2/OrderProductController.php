<?php
namespace App\Controller\v2;

/**
 * #40 HTTP codes: https://github.com/symfony/http-foundation/blob/master/Response.php
 */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use \App\v2\OrderProductCreator;
use \App\Exception\UidValidatorException;
use \App\Exception\ProductIdValidatorException;

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
	 * @SWG\Response(
	 *   response=404, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="string", example="Invalid product."),
	 *   )
	 * )
	 * @param OrderProductCreator $orderProductCreator
	 * @param int $customerId
	 * @param int $productId
	 * @return Response
	 */
	public function addProductToCart(OrderProductCreator $orderProductCreator, int $customerId, int $productId)
	{
		try {
			$orderProduct = $orderProductCreator->handle($customerId, $productId);
			return $this->json($orderProduct, Response::HTTP_CREATED);
		} catch (UidValidatorException | ProductIdValidatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}
}
