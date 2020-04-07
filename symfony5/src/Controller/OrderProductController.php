<?php
namespace App\Controller;

/**
 * #40 HTTP codes: https://github.com/symfony/http-foundation/blob/master/Response.php
 */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use \App\Order\OrderProductCreator;
use \App\Exception\UidValidatorException;
use \App\Exception\ProductIdValidatorException;


class OrderProductController extends AbstractController
{

	/**
	 * #40 #38 Add a product to customer's cart (draft order).
	 * #40 TODO: Should return bool for ENUM insteaf of y,n?.
	 * 
	 * @Route("/users/{customerId}/cart/{productId}", methods={"POST"})   
	 * @SWG\Tag(name="3. cart")
	 * @SWG\Response(
	 *   response=201, description="Created.",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example=1),
	 *    @SWG\Property(property="customer_id", type="integer", example=1),
	 *    @SWG\Property(property="order_id", type="integer", example=1),
	 *    @SWG\Property(property="seller_id", type="integer", example=1),
	 *    @SWG\Property(property="seller_title", type="string", example="John Doe"),
	 *    @SWG\Property(property="product_id", type="integer", example=1),
	 *    @SWG\Property(property="product_cost", type="integer", example=1000),
	 *    @SWG\Property(property="product_type", type="string", example="t-shirt"),
	 *    @SWG\Property(property="is_domestic", type="string", example="null"),
	 *    @SWG\Property(property="is_additional", type="string", example="null"),
	 *    @SWG\Property(property="is_express", type="string", example="null"),
	 *    @SWG\Property(property="shipping_cost", type="integer", example="null"),
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=404, description="Not found.",
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
			$resp = $orderProductCreator->handle($customerId, $productId)->toArray();
			return $this->json($resp, Response::HTTP_CREATED);
		} catch (UidValidatorException | ProductIdValidatorException $e) {
			return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
		}
	}
}
