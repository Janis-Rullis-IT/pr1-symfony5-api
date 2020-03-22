<?php
namespace App\Controller\v2;

/**
 * #40 HTTP codes: https://github.com/symfony/http-foundation/blob/master/Response.php
 */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
	 * #40 TODO: Add other fields.
	 * 
	 * @Route("/users/v2/{customerId}/cart/{productId}", methods={"POST"})   
	 * @SWG\Tag(name="3. cart")
	 * @SWG\Response(
	 *   response=201, description="Created.",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example="1"),
	 *    @SWG\Property(property="customer_id", type="integer", example="1"),
	 *    @SWG\Property(property="product_id", type="integer", example="1"),
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
