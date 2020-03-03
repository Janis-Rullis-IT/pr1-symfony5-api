<?php

namespace App\Controller;

use App\Exception\DuplicateException;
use App\Exception\UidValidatorException;
use App\Product\ProductCreator;
use App\Exception\ProductCreatorException;
use App\Interfaces\IProductRepo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class ProductController extends AbstractController
{
    /**
     * Create a new product for user.
     * 
     * - "type" : valid product types are "t-shirt" and "mug". They can be written in upper or lowercase e.g. "T-Shirt" is valid.
     * - "title" : can consist of upper and lowercase letters, digits, dash (-) and space ( ). It is stored as a string later on.
     * - "sku" : (stock keeping unit) must be unique among products user has submitted. It is stored as a string later on.
     * - "cost" : must be an integer representing cents
     * 
     * @Route("/users/{id_user}/products", name="createProduct", methods={"POST"})   
     * @SWG\Tag(name="product")
     * @SWG\Parameter(
	 *   name="body",
	 *   in="body",
	 *   required=true,
	 *   @SWG\Schema(
	 *    required={"type", "title", "sku", "cost"},
	 *    @SWG\Property(property="type", type="string", example="t-shirt"),
	 *    @SWG\Property(property="title", type="string", example="just do it"),
   *    @SWG\Property(property="sku", type="string", example="100-abc-999"),
   *    @SWG\Property(property="cost", type="integer", example=1000),
	 *   )
	 * )
	 * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example="1"),
   *    @SWG\Property(property="ownerId", type="integer", example="1"),
	 *    @SWG\Property(property="title", type="string", example="just do it"),
   *    @SWG\Property(property="sku", type="string", example="100-abc-999"),
   *    @SWG\Property(property="cost", type="integer", example=1000),
	 *   )
	 * )       
     * @param ProductCreator $createProductService
     * @param int $id_user
     * @return JsonResponse|Response
     */
    public function createProduct(ProductCreator $createProductService, int $id_user)
    {
        try {
            $createdProduct = $createProductService->handle($id_user);
            return $this->json($createdProduct, Response::HTTP_CREATED);
        } catch (UidValidatorException $e){
            return new Response(null,Response::HTTP_NOT_FOUND);
        } catch (DuplicateException $e){
            return $this->json($e->getErrors(), Response::HTTP_CONFLICT);
        } catch (ProductCreatorException $e){
            return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * View user's product.
     * 
     * @Route("/users/{id_user}/products/{id}", name="getProduct", methods={"GET"})
     * @SWG\Tag(name="product")
     * @SWG\Response(
	 *   response=200, description="",
	 *   @SWG\Schema(
	 *    @SWG\Property(property="id", type="integer", example="1"),
   *    @SWG\Property(property="ownerId", type="integer", example="1"),
	 *    @SWG\Property(property="title", type="string", example="just do it"),
   *    @SWG\Property(property="sku", type="string", example="100-abc-999"),
   *    @SWG\Property(property="cost", type="integer", example=1000),
	 *   )
	 * )      
     * @param IProductRepo $repo
     * @param int $id_user
     * @param int $id
     * @return JsonResponse|Response
     */

    public function getProductById(IProductRepo $repo, int $id_user, int $id)
    {
        $product = $repo->getById($id_user, $id);
        if ($product !== null)
            return $this->json($product, Response::HTTP_OK);
        return new Response(null,Response::HTTP_NOT_FOUND);
    }

    /**
     * View user's all products.
     
     * @Route("/users/{id_user}/products", name="getProducts", methods={"GET"})
     * @SWG\Tag(name="product")
     *
     * @SWG\Response(
	 *   response=200, description="",
	 *     @SWG\Schema(
	 *       type="array",
	 *       @SWG\Items(
	 *         type="object",
	 *         example = {
	 *           {"id": "1", "ownerId": 1, "type": "t-shirt", "title":  "just do it", "sku": "100-abc-999", "cost": 1000},
	 *           {"id": "2", "ownerId": 1, "type": "t-shirt", "title":  "lui v", "sku": "100-abc-100", "cost": 1000},
	 *         },
	 *         @SWG\Property(property="id", type="integer", example="1"),
   *         @SWG\Property(property="ownerId", type="integer", example="1"),
	 *         @SWG\Property(property="title", type="string", example="just do it"),
   *         @SWG\Property(property="sku", type="string", example="100-abc-999"),
   *         @SWG\Property(property="cost", type="integer", example=1000),
	 *       )
	 *    )
	 * )   
	 * 
     * @param IProductRepo $repo
     * @param int $id_user
     * @return JsonResponse|Response
     */

    public function getProducts(IProductRepo $repo, int $id_user)
    {
        $products = $repo->getAll($id_user);
        if ($products !== null)
            return $this->json($products, Response::HTTP_OK);
        return new Response(null,Response::HTTP_NOT_FOUND);
    }
}
