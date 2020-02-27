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

class ProductController extends AbstractController
{
    /**
     * @Route("/users/{id_user}/products", name="createProduct", methods={"POST"})
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
     * @Route("/users/{id_user}/products/{id}", name="getProduct", methods={"GET"})
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
     * @Route("/users/{id_user}/products", name="getProducts", methods={"GET"})
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
