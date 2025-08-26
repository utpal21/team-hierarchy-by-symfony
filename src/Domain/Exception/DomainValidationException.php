<?php
declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class DomainValidationException extends RuntimeException
{
    /** @var array<string, string[]> */
    private array $errors;

    /**
     * @param array<string, string[]> $errors
     * Example: ['file' => ['Missing upload'], 'format' => ['Only CSV allowed']]
     */
    public function __construct(array $errors, string $message = 'Validation Failed')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
