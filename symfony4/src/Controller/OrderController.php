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
use Swagger\Annotations as SWG;

class OrderController extends AbstractController
{
    /**
     * Create a new order for user.
     * 
     * 
     * `shipToAddress`
     * 
     * Orders can be either international or domestic (US).
     * 
     * Domestic order's "shipToAddress" must include all keys as in the example above, but for international orders "state" and "zip" keys are optional.
     * 
     * > "name" : accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apastrophe (') and dash (-).
     * 
     * > "surname" : accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apastrophe (') and dash (-).
     * 
     * > "street" : accepts upper and lowercase letters, spaces, dash (-) and digits.
     * 
     * > "state" : accepts all US states as shortform "NY" or long form "New York". Both can be written as a combination of upper and lowercase letters. "ny" and "new york" are both valid.
     * 
     * > "zip" : accepts the five digit and nine digit formats e.g. 12345 or 12345-6789 works, but not 1234 or 12345-67899.
     * 
     * > "country" : accepts two letter code "LV", three letter code "LVA" and name "Latvia". Made up countries are not accepted.
     * 
     * > "phone" : accepts a string or digits only. It is stored as a string later on.
     * 
     * `lineItems`
     * 
     * Each object within lineItems array represents a product within the order.
     * 
     * > "id" : id of the product user has created.
     * 
     * > "quantity" : how many units of the product are requested.
     * 
     * `info`
     * 
     * This part is only applicable to US orders and is optional. If `expressShipping` is set to `true`, then express shipping is enabled for the order. In that case, shipping costs for each product is 10$ (1000 cents).
     * 
     * - If the user has sufficient funds, order is created. Once it is created, funds from user are deducted.
     * 
     * @Route("/users/{id_user}/orders", name="createOrder", methods={"POST"})
     * @SWG\Tag(name="order")
     * @SWG\Parameter(
	 *   name="body",
	 *   in="body",
	 *   required=true,
	 *   @SWG\Schema(
	 *    required={"shipToAddress", "lineItems ", "info"},
	 *    @SWG\Property(property="shipToAddress", type="object", example={ "name" : "John", "surname" : "Doe", "street" : "Palm street 255", "state" : "NY", "zip" : "12315", "country" : "US", "phone" : "917-568-2970" }),
   *    @SWG\Property(property="lineItems", type="object", example={{"id": 1, "quantity": 1}, {"id": 2, "quantity": 1}}),
   *    @SWG\Property(property="info", type="object", example={"expressShipping": true }),
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="shipToAddress", type="object", example={ "name": "John", "surname": "Doe", "street": "Palm Street 255", "state" : "NY", "zip" : "12315", "country": "US", "phone": "917-568-2970" }),
   *    @SWG\Property(property="lineItems", type="object", example={{ "id": 1, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "just do it", "sku": "100-abc-999", "cost": 1000, "totalCost": 1000 }, { "id": 2, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "mo mamba", "sku": "100-abc-100", "cost": 1000, "totalCost": 1000 }}),
   *    @SWG\Property(property="info", type="object", example={ "id": 3, "ownerId": 1, "productionCost": 2000, "shippingCost": 2000, "expressShipping": true, "totalCost": 4000 }),
	 *   )
	 * )       
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
     * View user's order.
     * 
     * @Route("/users/{id_user}/orders/{id}", name="viewOrder", methods={"GET"})
     * @SWG\Tag(name="order")
     * 
     * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="shipToAddress", type="object", example={ "name": "John", "surname": "Doe", "street": "Palm Street 255", "state" : "NY", "zip" : "12315", "country": "US", "phone": "917-568-2970" }),
   *    @SWG\Property(property="lineItems", type="object", example={{ "id": 1, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "just do it", "sku": "100-abc-999", "cost": 1000, "totalCost": 1000 }, { "id": 2, "quantity": 1, "ownerId": 1, "type": "t-shirt", "title": "mo mamba", "sku": "100-abc-100", "cost": 1000, "totalCost": 1000 }}),
   *    @SWG\Property(property="info", type="object", example={ "id": 3, "ownerId": 1, "productionCost": 2000, "shippingCost": 2000, "expressShipping": true, "totalCost": 4000 }),
	 *   )
	 * )  
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
     *  View user's all orders.
     * 
     * @Route("/users/{id_user}/orders", name="viewOrders", methods={"GET"})
     * @SWG\Tag(name="order")
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
        return new Response(null,Response::HTTP_NOT_FOUND);
    }
}
