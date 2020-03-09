<?php
namespace App\v2;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use \App\Entity\v2\OrderProduct;

class OrderProductValidator
{

	/**
	 * #38 Check if all required fields are passed.
	 * 
	 * @param array $data
	 * @return true|array $errors
	 */
	public function hasRequiredKeys(array $data)
	{
		$errors = [];

		foreach (OrderProduct::$requireds as $requiredKey) {
			if (!isset($data[$requiredKey])) {
				$errors[$requiredKey] = "'$requiredKey'" . ' field is missing.';
			}
		}

		return empty($errors) ? true : $errors;
	}

	public function validate(array $data): void
	{


//		// #38 https://symfony.com/doc/current/validation.html
//		$validator = Validation::createValidator();
//		$violations = $validator->validate('Bernhard', [
//			new Length(['min' => 10]),
//			new NotBlank(),
//		]);
//		}
//
//
//		if (empty($errors)) {
//			return response()->json(['data' => \App\Models\Product::findBySlug($slug, $type), 'success' => true], 200);
//		} else {
//			return response()->json(['errors' => $errors], 400);
//		}




		dd($data);
	}

	public function getErrors(): array
	{
		return $this->errors;
	}
}
