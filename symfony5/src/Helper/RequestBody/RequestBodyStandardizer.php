<?php

namespace App\Helper\RequestBody;

use App\Entity\Product;
use App\Entity\User;

class RequestBodyStandardizer
{
    public function standardize(array $requestBody): array
    {
        /* If user's name or surname keys are written in another case or some letters are lower/upper case, correct it */
        foreach ($requestBody as $key => $value) {
            if (preg_match('/^'.User::NAME.'$/i', $key) && User::NAME !== $key) {
                $requestBody[User::NAME] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'.User::SURNAME.'$/i', $key) && User::SURNAME !== $key) {
                $requestBody[User::SURNAME] = $value;
                unset($requestBody[$key]);
            }
        }

        /* Capitalize first and lowercase rest of each word within name */
        if (array_key_exists(User::NAME, $requestBody)) {
            $requestBody[User::NAME] = ucwords(strtolower($requestBody[User::NAME]));
        }

        /* Capitalize first and lowercase rest of each word within name */
        if (array_key_exists(User::SURNAME, $requestBody)) {
            $requestBody[User::SURNAME] = ucwords(strtolower($requestBody[User::SURNAME]));
        }

        /* If products's type, title, sku or cost keys are written in another case or some letters are lower/upper case, correct it */
        foreach ($requestBody as $key => $value) {
            if (preg_match('/^'.Product::TYPE.'$/i', $key) && Product::TYPE !== $key) {
                $requestBody[Product::TYPE] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'.Product::TITLE.'$/i', $key) && Product::TITLE !== $key) {
                $requestBody[Product::TITLE] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'.Product::SKU.'$/i', $key) && Product::SKU !== $key) {
                $requestBody[Product::SKU] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'.Product::COST.'$/i', $key) && Product::COST !== $key) {
                $requestBody[Product::COST] = $value;
                unset($requestBody[$key]);
            }
        }

        /* lower case product type */
        if (array_key_exists(Product::TYPE, $requestBody)) {
            $requestBody[Product::TYPE] = strtolower($requestBody[Product::TYPE]);
        }

        return $requestBody;
    }
}
