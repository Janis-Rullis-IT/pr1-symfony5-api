<?php


namespace App\Order;

use App\Entity\Order;
use App\Entity\Product;
use App\ErrorsLoader;
use App\Exception\OrderValidatorException;
use App\Interfaces\IProductRepo;
use App\Validators\AddressValidators\AddressValidator;

class OrderValidator
{
    private $addressValidator;
    private $repository;
    private $errors;
    private $lineItemsErrors;
    private $errorsLoader;

    public function __construct(AddressValidator $addressValidator, IProductRepo $repository, ErrorsLoader $errorsLoader)
    {
        $this->addressValidator = $addressValidator;
        $this->repository = $repository;
        $this->errors = [];
        $this->lineItemsErrors = [];
        $this->errorsLoader = $errorsLoader;
    }

    public function validate(int $id_user, array $request_body): void
    {
        if (!array_key_exists(Order::SHIPPING_DATA, $request_body))
            $this->errors[Order::SHIPPING_DATA] = Order::NO_SHIPPING_DATA;
        if (!array_key_exists(Order::LINE_ITEMS, $request_body))
            $this->errors[Order::LINE_ITEMS] = Order::NO_LINE_ITEMS;

        if (array_key_exists(Order::SHIPPING_DATA, $request_body) && !$this->addressValidator->validate($request_body[Order::SHIPPING_DATA]))
            $this->errors[Order::SHIPPING_DATA] = $this->addressValidator->getErrors();
        if (array_key_exists(Order::LINE_ITEMS, $request_body) && !$this->validateLineItems($id_user, $request_body[Order::LINE_ITEMS]))
            $this->errors[Order::LINE_ITEMS] = $this->getLineItemsErrors();

        if (!empty($this->errors))
            throw new OrderValidatorException($this->errors);
    }

    public function validateLineItems(int $id_user, array $line_items): bool
    {
        $itemNumber = 1;

        foreach ($line_items as $item)
        {
            if (!array_key_exists(Product::ID, $item))
                $this->errorsLoader->load(Product::ID, Product::NO_LINE_ITEM_ID . $itemNumber, $this->lineItemsErrors);
            if (!array_key_exists(Product::QUANTITY, $item))
                $this->errorsLoader->load(Product::QUANTITY, Product::NO_LINE_ITEM_QUANTITY . $itemNumber, $this->lineItemsErrors);
            if (array_key_exists(Product::ID, $item) && !$this->repository->getById($id_user, $item[Product::ID]))
                $this->errorsLoader->load(Product::ID, Product::INVALID_LINE_ITEM_ID . $itemNumber, $this->lineItemsErrors);
            if (array_key_exists(Product::QUANTITY, $item) && $item[Product::QUANTITY] <= 0)
                $this->errorsLoader->load(Product::QUANTITY, Product::INVALID_LINE_ITEM_QUANTITY . $itemNumber, $this->lineItemsErrors);
            $itemNumber++;
        }

        if (count($line_items) === 0)
            $this->lineItemsErrors[Order::LINE_ITEMS] = Order::EMPTY_ORDER;

        if (!empty($this->lineItemsErrors))
            return (false);
        return (true);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLineItemsErrors(): array
    {
        return $this->lineItemsErrors;
    }
}