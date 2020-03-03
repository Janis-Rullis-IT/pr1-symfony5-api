<?php


namespace App\Order;

use App\CostCalculator\CostCalculator;
use App\Entity\Order;
use App\Entity\User;
use App\Exception\OrderCreatorException;
use App\Exception\FundManagerException;
use App\Exception\JsonToArrayException;
use App\Exception\OrderValidatorException;
use App\Exception\UidValidatorException;
use App\FundManager\FundManager;
use App\Interfaces\IRelationRepo;
use App\Interfaces\IOrderRepo;
use App\RequestBody\JsonToArray;
use App\Validators\UserValidators\UidValidator;

class OrderCreator
{
    private $converter;
    private $repo;
    private $relationRepo;
    private $uidValidator;
    private $validator;
    private $calculator;
    private $manager;

    public function __construct(JsonToArray $converter, IOrderRepo $repo, IRelationRepo $relationRepo, UidValidator $uidValidator, OrderValidator $validator, CostCalculator $calculator, FundManager $manager)
    {
        $this->converter = $converter;
        $this->repo = $repo;
        $this->relationRepo = $relationRepo;
        $this->uidValidator = $uidValidator;
        $this->validator = $validator;
        $this->calculator = $calculator;
        $this->manager = $manager;
    }

    public function handle(int $id_user): array
    {
        if (!$this->uidValidator->validate($id_user))
            throw new UidValidatorException([User::ID => User::INVALID]);

        try {
            $requestBody = $this->converter->retrieve();
            $this->validator->validate($id_user, $requestBody);
            $costs = $this->calculator->calculate($id_user, $requestBody);
            $this->manager->userPay($id_user, $costs[Order::TOTAL_COST]);
            $newOrder = $this->repo->create($requestBody, $costs, $id_user);
            $this->relationRepo->create($newOrder->getId(), $requestBody[Order::LINE_ITEMS], $id_user);
        } catch (JsonToArrayException $e) {
            throw new OrderCreatorException($e->getErrors());
        } catch (OrderValidatorException $e){
            throw new OrderCreatorException($e->getErrors());
        } catch (FundManagerException $e){
            throw new OrderCreatorException($e->getErrors());
        }
        return $this->repo->getById($id_user, $newOrder->getId());
    }
}