<?php


namespace App\FundManager;


use App\Exception\FundManagerException;
use App\Interfaces\IUserRepo;

class FundManager
{
    const BALANCE = "balance";
    const INSUFFICIENT_FUNDS = "not sufficient funds";

    private $repository;

    public function __construct(IUserRepo $repository)
    {
        $this->repository = $repository;
    }

    public function userPay(int $id_user, int $amount): void
    {
        if ($this->sufficientFunds($id_user, $amount))
            $this->subtractBalance($id_user, $amount);
        else
            throw new FundManagerException([FundManager::BALANCE => FundManager::INSUFFICIENT_FUNDS]);
    }

    private function sufficientFunds(int $id_user, int $amount): bool
    {
        $user = $this->repository->getById($id_user);
        if ($user->getBalance() >= $amount)
            return (true);
        return (false);
    }

    private function subtractBalance(int $id_user, int $amount): void
    {
        $user = $this->repository->getById($id_user);
        $user->subtractBalance($amount);
    }


}