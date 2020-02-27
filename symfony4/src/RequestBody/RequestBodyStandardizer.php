<?php


namespace App\RequestBody;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;

class RequestBodyStandardizer
{
    public function standardize(array $requestBody): array
    {
        /* If user's name or surname keys are written in another case or some letters are lower/upper case, correct it */
        foreach ($requestBody as $key => $value)
        {
            if (preg_match('/^'. User::NAME . '$/i', $key) && $key !== User::NAME)
            {
                $requestBody[User::NAME] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'. User::SURNAME . '$/i', $key) && $key !== User::SURNAME)
            {
                $requestBody[User::SURNAME] = $value;
                unset($requestBody[$key]);
            }
        }

        /* Capitalize first and lowercase rest of each word within name */
        if (array_key_exists(User::NAME, $requestBody))
        {
            $requestBody[User::NAME] = ucwords(strtolower($requestBody[User::NAME]));
        }

        /* Capitalize first and lowercase rest of each word within name */
        if (array_key_exists(User::SURNAME, $requestBody))
        {
            $requestBody[User::SURNAME] = ucwords(strtolower($requestBody[User::SURNAME]));
        }

        /* If products's type, title, sku or cost keys are written in another case or some letters are lower/upper case, correct it */
        foreach ($requestBody as $key => $value)
        {
            if (preg_match('/^'. Product::TYPE . '$/i', $key) && $key !== Product::TYPE)
            {
                $requestBody[Product::TYPE] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'. Product::TITLE . '$/i', $key) && $key !== Product::TITLE)
            {
                $requestBody[Product::TITLE] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'. Product::SKU . '$/i', $key) && $key !== Product::SKU)
            {
                $requestBody[Product::SKU] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'. Product::COST . '$/i', $key) && $key !== Product::COST)
            {
                $requestBody[Product::COST] = $value;
                unset($requestBody[$key]);
            }
        }

        /* lower case product type */
        if (array_key_exists(Product::TYPE, $requestBody))
        {
            $requestBody[Product::TYPE] = strtolower($requestBody[Product::TYPE]);
        }

        /* If shipToAddress, lineItems or info are written in another case or some letters are lower/upper case, correct it */
        foreach ($requestBody as $key => $value)
        {
            if (preg_match('/^'. Order::SHIPPING_DATA . '$/i', $key) && $key !== Order::SHIPPING_DATA)
            {
                $requestBody[Order::SHIPPING_DATA] = $value;
                unset($requestBody[$key]);
            } elseif(preg_match('/^'. Order::LINE_ITEMS . '$/i', $key) && $key !== Order::LINE_ITEMS)
            {
                $requestBody[Order::LINE_ITEMS] = $value;
                unset($requestBody[$key]);
            } elseif (preg_match('/^'. Order::INFO . '$/i', $key) && $key !== Order::INFO)
            {
                $requestBody[Order::INFO] = $value;
                unset($requestBody[$key]);
            }
        }

        if (array_key_exists(Order::SHIPPING_DATA, $requestBody))
        {
            /* lower case shippingData keys */
            $requestBody[Order::SHIPPING_DATA] = array_change_key_case($requestBody[Order::SHIPPING_DATA], CASE_LOWER);

            /* Capital first letters for all words of OWNER_NAME */
            if (array_key_exists(Order::OWNER_NAME, $requestBody[Order::SHIPPING_DATA]))
            {
                $requestBody[Order::SHIPPING_DATA][Order::OWNER_NAME] = ucwords(strtolower($requestBody[Order::SHIPPING_DATA][Order::OWNER_NAME]));
            }

            /* Capital first letters for all words of OWNER_SURNAME */
            if (array_key_exists(Order::OWNER_SURNAME, $requestBody[Order::SHIPPING_DATA]))
            {
                $requestBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME] = ucwords(strtolower($requestBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME]));
            }

            /* Capital first letters for all words of STREET */
            if (array_key_exists(Order::STREET, $requestBody[Order::SHIPPING_DATA]))
            {
                $requestBody[Order::SHIPPING_DATA][Order::STREET] = ucwords(strtolower($requestBody[Order::SHIPPING_DATA][Order::STREET]));
            }

            /* If state is written in short form, upper case it. Otherwise, capitalize each word of long form. */
            if (array_key_exists(Order::STATE, $requestBody[Order::SHIPPING_DATA]))
            {
                if (strlen($requestBody[Order::SHIPPING_DATA][Order::STATE]) > 2)
                    $requestBody[Order::SHIPPING_DATA][Order::STATE] = ucwords(strtolower($requestBody[Order::SHIPPING_DATA][Order::STATE]));
                else
                    $requestBody[Order::SHIPPING_DATA][Order::STATE] = strtoupper($requestBody[Order::SHIPPING_DATA][Order::STATE]);
            }

            /* If country is written in short form, upper case it. Otherwise, capitalize each word of long form. */
            if (array_key_exists(Order::COUNTRY, $requestBody[Order::SHIPPING_DATA]))
            {
                if (strlen($requestBody[Order::SHIPPING_DATA][Order::COUNTRY]) > 3)
                    $requestBody[Order::SHIPPING_DATA][Order::COUNTRY] = ucwords(strtolower($requestBody[Order::SHIPPING_DATA][Order::COUNTRY]));
                else
                    $requestBody[Order::SHIPPING_DATA][Order::COUNTRY] = strtoupper($requestBody[Order::SHIPPING_DATA][Order::COUNTRY]);
            }
        }

        /* lower case keys within each line item within lineItems array */
        if (array_key_exists(Order::LINE_ITEMS, $requestBody))
        {
            $count = count($requestBody[Order::LINE_ITEMS]);
            for ($i = 0; $i < $count; $i++)
            {
                $requestBody[Order::LINE_ITEMS][$i] = array_change_key_case($requestBody[Order::LINE_ITEMS][$i], CASE_LOWER);
            }
        }

        /* match "expressShipping" having written in different way e.g. "ExpressShipping" and make it as "expressShipping" */
        if (array_key_exists(Order::INFO, $requestBody))
        {
            foreach ($requestBody[Order::INFO] as $key => $value)
            {
                if (preg_match('/^' . Order::EXPRESS_SHIPPING . '$/i', $key) && $key !== Order::EXPRESS_SHIPPING)
                {
                    $requestBody[Order::INFO][Order::EXPRESS_SHIPPING] = $value;
                    unset($requestBody[Order::INFO][$key]);
                }
            }
        }
        return $requestBody;
    }
}