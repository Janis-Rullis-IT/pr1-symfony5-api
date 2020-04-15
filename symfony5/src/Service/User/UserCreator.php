<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\JsonToArrayException;
use App\Exception\UserCreatorException;
use App\Exception\UserValidatorException;
use App\Helper\RequestBody\JsonToArray;
use App\Interfaces\IUserRepo;

class UserCreator
{
    private $converter;
    private $repo;
    private $validator;

    public function __construct(JsonToArray $converter, IUserRepo $repo, UserValidator $validator)
    {
        $this->converter = $converter;
        $this->repo = $repo;
        $this->validator = $validator;
    }

    public function handle(): User
    {
        try {
            $requestBody = $this->converter->retrieve();
            $this->validator->validate($requestBody);
            $newUser = $this->repo->create($requestBody);
        } catch (JsonToArrayException $e) {
            throw new UserCreatorException($e->getErrors());
        } catch (UserValidatorException $e) {
            throw new UserCreatorException($e->getErrors());
        }

        return $newUser;
    }
}
