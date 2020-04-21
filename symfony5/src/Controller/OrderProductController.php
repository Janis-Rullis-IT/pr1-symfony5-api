<?php

namespace App\Controller;

/*
 * #40 HTTP codes: https://github.com/symfony/http-foundation/blob/master/Response.php
 */
use App\Entity\OrderProduct;
use App\Exception\ProductIdValidatorException;
use App\Exception\UidValidatorException;
use App\Service\Order\OrderProductCreator;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderProductController extends AbstractController
{
    /**
     * Add a product to customer's cart (draft order).
     * #40 TODO: Should return bool for ENUM insteaf of y,n?.
     * #49 TODO: Handle 404 return value.
     *
     * @Route("/users/{customerId}/cart/{productId}", methods={"POST"})
     * @SWG\Tag(name="3. cart")
     *
     * @SWG\Response(response=200, description="Created.", @SWG\Schema(type="object", ref=@Model(type=OrderProduct::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=OrderProduct::class, groups={"ID_ERROR"})))
     */
    public function addProductToCart(OrderProductCreator $orderProductCreator, int $customerId, int $productId): JsonResponse
    {
        try {
            $resp = $orderProductCreator->handle($customerId, $productId)->toArray();

            return $this->json($resp, Response::HTTP_CREATED);
        } catch (UidValidatorException | ProductIdValidatorException $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
            }

            return $this->json($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
