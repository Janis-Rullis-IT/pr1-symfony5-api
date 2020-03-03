<?php


namespace App\Product;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\DuplicateException;
use App\Exception\ProductCreatorException;
use App\Exception\JsonToArrayException;
use App\Exception\UidValidatorException;
use App\Exception\ProductValidatorException;
use App\Interfaces\IProductRepo;
use App\RequestBody\JsonToArray;
use App\Validators\UserValidators\UidValidator;

class ProductCreator
{
    private $converter;
    private $repo;
    private $uidValidator;
    private $validator;

    public function __construct(JsonToArray $converter, IProductRepo $repository, UidValidator $uidValidator, ProductValidator $validator)
    {
        $this->converter = $converter;
        $this->repo = $repository;
        $this->uidValidator = $uidValidator;
        $this->validator = $validator;
    }

    public function handle(int $id_user): Product
    {
        if (!$this->uidValidator->validate($id_user))
            throw new UidValidatorException([User::ID => User::INVALID]);

        try {
            $requestBody = $this->converter->retrieve();
            $this->validator->validate($requestBody, $id_user);
            $newProduct = $this->repo->create($id_user, $requestBody);
        } catch (JsonToArrayException $e) {
            throw new ProductCreatorException($e->getErrors());
        } catch (DuplicateException $e){
            throw $e;
        } catch (ProductValidatorException $e) {
            throw new ProductCreatorException($e->getErrors());
        }
        return $newProduct;
    }
}