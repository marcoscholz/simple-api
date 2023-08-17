<?php

namespace MarcoScholz\SimpleApi;

use Throwable;

/**
 * @package MarcoScholz\SimpleApi
 * @author Marco Scholz <mail@marco-scholz.com>
 * @version tbd
 *
 * Routes incoming requests to the appropriate handler based on defined patterns.
 */
class Router extends Endpoint
{
    /** @var string Base namespace for the router. */
    readonly public string $namespace;

    /**
     * Initializes the router, tests request patterns, and runs the appropriate method or throws an error.
     *
     * @param string $namespace Base namespace for the router.
     * @param Request[] $requestPatterns Array of request patterns to match against.
     */
    public function __construct(string $namespace, array $requestPatterns)
    {
        parent::__construct();

        $this->namespace = rtrim($namespace, '/');

        if (!str_starts_with($this->uri, $this->namespace)) return;

        // OPTIONS
        if ($this->method === "OPTIONS") {
            $options = [];
            foreach ($requestPatterns as $patternDto) {
                $match = $this->match($patternDto);
                if ($match) {
                    $options[$patternDto->method] = $patternDto->method;
                }
            }

            $this->returnOptions($options);
        }

        // match pattern and break on first match
        foreach ($requestPatterns as $patternDto) {
            $match = $this->match($patternDto);
            if ($match) {
                $this->run($match);
            }
        }

        // request is not covered by given RequestPatterns
        $this->throwJsonError(
            errorCode: 404,
            msg: "Endpoint not found",
            details: [
                "uri" => $this->uri,
                "path" => $this->path,
                "query" => $this->query
            ]
        );
    }

    /**
     * Match request against pattern
     * - returns enriched RequestPatternDto on success
     * - return false if pattern doesn't match
     * @param Request $patternDto The request pattern to match against.
     * @return Request|false Returns the enriched RequestPatternDto on success, or false if the pattern doesn't match.
     */
    protected function match(Request $patternDto): Request|false
    {
        // Skip pattern if wrong Request Method GET/POST/...
        if ($this->method !== $patternDto->method && $this->method !== "OPTIONS") {
            return false;
        }

        // Check if uri matches pattern
        preg_match($patternDto->regex, $this->path, $match, PREG_UNMATCHED_AS_NULL);
        if (empty($match)) {
            return false;
        }

        // Map params
        foreach ($patternDto->params as $name => $datatype) {
            $patternDto->params[$name] = $match[$name] ?? null;
            if ($patternDto->params[$name] !== null) {
                settype($patternDto->params[$name], $datatype);
            }
        }

        $patternDto->params['query'] = [];
        foreach (explode("&", str_replace('&amp;', '&', $this->query)) as $queryItem) {
            $parts = explode("=", $queryItem);
            $key = $parts[0];
            if (strlen($key) === 0) {
                continue;
            }

            $value = true;
            if (array_key_exists(1, $parts)) {
                $value = is_numeric($parts[1]) ? ((int)$parts[1]) : $parts[1];
            }

            $patternDto->params['query'][$key] = $value;
        }

        return $patternDto;
    }

    /**
     * Executes the matched handler based on the parsed parameters.
     *
     * @param Request $match The matched request pattern.
     * @return never
     */
    protected function run(Request $match): never
    {
        $className = $match->handlerClass;
        $method = $match->handlerMethod;

        try {
            // Check if class and method exist
            $this->checkIfClassExists($className);
            $this->checkIfMethodExists($className, $method);
            $handler = new $className($match->params);
            if ($method !== null) {
                $handler->$method($match->params);
            }

        } catch (ApiException $apiException) {
            $this->throwJsonError(
                errorCode: $apiException->getCode(),
                msg: $apiException->getMessage(),
                details: $apiException->getDetails()
            );
        } catch (Throwable $e) {
            $this->throwJsonError(
                errorCode: 500,
                msg: "Server Error: While calling method '$method'",
                details: [
                    "class" => $className,
                    "method" => $method,
                    "errorMessage" => $e->getMessage(),
                    "trace" => $e->getTrace()
                ]
            );
        }

        exit;
    }

    /**
     * Checks if the specified class exists.
     *
     * @param string $className Name of the class to check.
     * @return void
     * @throws ApiException If the class does not exist.
     */
    private function checkIfClassExists(string $className): void
    {
        if (!class_exists($className)) {
            throw new ApiException(
                message: "Server Error: Missing class",
                code: 500,
                details: ["class" => $className]
            );
        }
    }

    /**
     * Checks if the specified method exists in the given class.
     *
     * @param string $className Name of the class.
     * @param string|null $method Name of the method to check.
     * @return void
     * @throws ApiException If the method does not exist in the class.
     */
    private function checkIfMethodExists(string $className, ?string $method = null): void
    {
        if ($method !== null && !method_exists($className, $method)) {
            throw new ApiException(
                message: "Server Error: Method '$method' not found",
                code: 500,
                details: ["class" => $className, "method" => $method]
            );
        }
    }
}
