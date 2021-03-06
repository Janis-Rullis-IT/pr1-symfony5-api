<?php
/**
 * #40 Doc Annotations https://symfony.com/doc/current/bundles/NelmioApiDocBundle/faq.html.
 */

namespace App\Controller;

use App\Entity\Order;
use App\Exception\OrderValidatorException;
use App\Exception\UidValidatorException;
use App\Repository\OrderRepository;
use App\Service\Order\OrderService;
use App\Service\Order\OrderShippingService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * Set order's shipping.
     *
     * @Route("/users/{customerId}/order/shipping", methods={"PUT"})
     * @SWG\Tag(name="4. shipping")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"name", "surname", "street", "country", "phone", "is_express"}, type="object", ref=@Model(type=Order::class, groups={"CREATE"})))
     * @SWG\Response(response=200, description="Created.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"ID_ERROR"})))
     */
    public function setShipping(Request $request, OrderShippingService $orderShippingService, int $customerId): JsonResponse
    {
        try {
            $resp = $orderShippingService->set($customerId, json_decode($request->getContent(), true))->toArray([], [Order::PRODUCTS]);

            return $this->json($resp, Response::HTTP_OK);
        } catch (UidValidatorException $e) {
            return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Complete the order.
     *
     * @Route("/users/{customerId}/order/complete", methods={"PUT"})
     * @SWG\Tag(name="5. complete order")
     *
     * @SWG\Response(response=200, description="Saved.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"ID_ERROR"})))
     */
    public function complete(Request $request, OrderService $orderService, int $customerId): JsonResponse
    {
        try {
            $resp = $orderService->complete($customerId)->toArray([], [Order::PRODUCTS]);

            return $this->json($resp, Response::HTTP_OK);
        } catch (UidValidatorException $e) {
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

    /**
     * View user's order.
     *
     * @Route("/users/{id_user}/orders/{id}", methods={"GET"})
     * @SWG\Tag(name="6. order")
     *
     * @SWG\Response(response=200, description="", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"ID_ERROR"})))
     */
    public function getUsersOrderById(OrderRepository $repo, int $id_user, int $id): JsonResponse
    {
        try {
            $resp = $repo->mustFindUsersOrder($id_user, $id)->toArray([], [Order::PRODUCTS]);

            return $this->json($resp, Response::HTTP_OK);
        } catch (OrderValidatorException $e) {
            return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * View all user's orders.
     *
     * @Route("/users/{id_user}/orders", methods={"GET"})
     * @SWG\Tag(name="6. order")
     *
     * @SWG\Response(response=200, description="", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Order::class, groups={"ID_ERROR"})))
     */
    public function getUsersOrders(OrderRepository $repo, int $id_user): JsonResponse
    {
        try {
            $resp = [];
            $orders = $repo->mustFindUsersOrders($id_user);
            foreach ($orders as $order) {
                $resp[] = $order->toArray([], [Order::PRODUCTS]);
            }

            return $this->json($resp, Response::HTTP_OK);
        } catch (OrderValidatorException $e) {
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
