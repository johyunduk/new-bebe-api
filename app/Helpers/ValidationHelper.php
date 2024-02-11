<?php

namespace App\Helpers;

use Illuminate\Support\Optional;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ValidationHelper
{
    public static function checkValidator(Validator $validator)
    {
        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        return $validator->getData();
    }
}
