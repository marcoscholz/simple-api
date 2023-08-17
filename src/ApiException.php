<?php

namespace MarcoScholz\SimpleApi;

use Exception;

/**
 * @package MarcoScholz\SimpleApi
 * @author Marco Scholz <mail@marco-scholz.com>
 * @version tbd
 *
 * Custom exception class for API-related errors.
 */
class ApiException extends Exception
{
    /**
     * ApiException constructor.
     *
     * @param string $message The exception message.
     * @param int $code The exception code (default is 500).
     * @param Exception|null $previous The previous exception (if any).
     * @param array $details Additional details related to the exception.
     */
    public function __construct(
        protected       $message = "",
        protected       $code = 500,
        protected       $previous = null,
        protected array $details = []
    )
    {
        parent::__construct($message, $code, $this->previous);
    }

    /**
     * Retrieves the details associated with the exception.
     *
     * @return array An array of details related to the exception.
     */
    final public function getDetails(): array
    {
        return $this->details;
    }
}
