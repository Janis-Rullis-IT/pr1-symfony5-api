<?php

namespace App\Controller;

use App\Exception\UidValidatorException;
use App\Order\OrderCreator;
use App\Exception\OrderCreatorException;
use App\Interfaces\IOrderRepo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/users/{id_user}/orders", name="createOrder", methods={"POST"})
     * @param OrderCreator $createOrderService
     * @param int $id_user
     * @return JsonResponse|Response
     */
    public function createOrder(OrderCreator $createOrderService, int $id_user)
    {
        try {
            $createdOrder = $createOrderService->handle($id_user);
            return $this->json($createdOrder, Response::HTTP_CREATED);
        } catch (UidValidatorException $e){
            return new Response(null,Response::HTTP_NOT_FOUND);
        } catch (OrderCreatorException $e){
            return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/users/{id_user}/orders/{id}", name="viewOrder", methods={"GET"})
     * @param IOrderRepo $repo
     * @param int $id_user
     * @param int $id
     * @return JsonResponse|Response
     */
    public function getOrderById(IOrderRepo $repo, int $id_user, int $id)
    {
        $order = $repo->getById($id_user, $id);
        if ($order !== null)
            return $this->json($order, Response::HTTP_OK);
        return new Response(null,Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/users/{id_user}/orders", name="viewOrders", methods={"GET"})
     * @param IOrderRepo $repo
     * @param int $id_user
     * @return JsonResponse|Response
     */
    public function getOrders(IOrderRepo $repo, int $id_user)
    {
        $orders = $repo->getAll($id_user);
        if ($orders !== null)
            return $this->json($orders, Response::HTTP_OK);
        return new Response(null,Response::HTTP_NOT_FOUND);
    }
}
