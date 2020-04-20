<?php

namespace App\Interfaces;

use App\Entity\Order;

interface IOrderRepo
{
    public function insertDraftIfNotExist(int $customerId): Order;

    public function getCurrentDraft(int $customerId): ?Order;

    public function setOrderCostsFromCartItems(Order $order): bool;

    public function fillShipping(Order $order, array $shippingData): Order;

    public function save();

    public function markAsCompleted(Order $order): Order;

    public function mustFindUsersOrder(int $userId, int $orderId): Order;

    public function mustFindUsersOrders(int $userId): array;
}
