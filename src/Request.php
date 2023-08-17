<?php

namespace MarcoScholz\SimpleApi;

/**
 * @package MarcoScholz\SimpleApi
 * @author Marco Scholz <mail@marco-scholz.com>
 * @version tbd
 *
 * Represents an API request pattern.
 *
 * This class is used to define and manage API request patterns, which include the HTTP method,
 * the URL pattern, the handler class, and the handler method. It also provides utility methods
 * to create request patterns for specific HTTP methods and to apply a namespace to the pattern.
 */
class Request
{
    /** @var string HTTP method for the request pattern. */
    readonly public string $method;

    /** @var string URL pattern for the request. */
    readonly public string $pattern;

    /** @var class-string Fully qualified class name of the handler. */
    readonly public string $handlerClass;

    /** @var callable-string|null Method name of the handler. */
    readonly public ?string $handlerMethod;

    /** @var string Regular expression derived from the URL pattern. */
    public string $regex = "uninitialized";

    /** @var array Parameters extracted from the URL pattern. */
    public array $params;

    /**
     * Constructs a new request pattern.
     *
     * @param string $method HTTP method.
     * @param string $pattern URL pattern.
     * @param class-string $handlerClass Handler class.
     * @param callable-string|null $handlerMethod Handler method.
     */
    public function __construct(string $method, string $pattern, string $handlerClass, ?string $handlerMethod = null)
    {
        $this->method = $method;
        $this->handlerClass = $handlerClass;
        $this->handlerMethod = $handlerMethod;
        $this->pattern = $pattern;
        $this->params = [];

        $this->extractParams();
    }

    /**
     * Extracts parameters from the URL pattern.
     *
     * Parameters are defined in the format {name::datatype} within the URL pattern.
     * - Datatype can be int|float|string (string is default)
     *
     * @return string The generated regular expression.
     */
    protected function extractParams(): string
    {
        $paramPattern = '/\{(([a-zA-Z_]*)(?>::int|float)?)}/';
        $this->regex = "/" . str_replace('/', '\/', $this->pattern) . "$/";
        preg_match_all($paramPattern, $this->regex, $params);

        foreach ($params[1] as $param) {
            $split = explode("::", $param);
            $name = $split[0];
            $datatype = $split[1] ?? 'string';
            $this->params[$name] = $datatype;
        }

        $this->regex = preg_replace($paramPattern, "(?<\$2>.*)", $this->regex);

        return $this->regex;
    }

    /**
     * Creates a GET request pattern.
     *
     * @param string $pattern URL pattern.
     * @param string $handlerClass Handler class.
     * @param callable-string|null $handlerMethod Handler method.
     * @return static
     */
    static public function GET(string $pattern, string $handlerClass, ?string $handlerMethod = null) : Request
    {
        return new static("GET", $pattern, $handlerClass, $handlerMethod);
    }

    /**
     * Creates a POST request pattern.
     *
     * @param string $pattern URL pattern.
     * @param string $handlerClass Handler class.
     * @param callable-string|null $handlerMethod Handler method.
     * @return static
     */
    static public function POST(string $pattern, string $handlerClass, ?string $handlerMethod = null) : Request
    {
        return new static("POST", $pattern, $handlerClass, $handlerMethod);
    }

    /**
     * Creates a PUT request pattern.
     *
     * @param string $pattern URL pattern.
     * @param string $handlerClass Handler class.
     * @param callable-string|null $handlerMethod Handler method.
     * @return static
     */
    static public function PUT(string $pattern, string $handlerClass, ?string $handlerMethod = null) : Request
    {
        return new static("PUT", $pattern, $handlerClass, $handlerMethod);
    }

}
