<?php

namespace App\Exceptions;

class ValidationException extends \Exception
{
    protected $errors = [];
    
    public function __construct(array $errors, $message = "Validation error", $code = 422)
    {
        $this->errors = $errors;
        parent::__construct($message, $code);
    }
    
    public function getErrors()
    {
        return $this->errors;
    }

    public static function forField($field, $message, $code = 422)
    {
        return new self([$field => [$message]], $message, $code);
    }
}
