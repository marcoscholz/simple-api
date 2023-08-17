<?php

namespace MarcoScholz\SimpleApi;

/**
 * @package MarcoScholz\SimpleApi
 * @author Marco Scholz <mail@marco-scholz.com>
 * @version tbd
 *
 * Represents an API endpoint with utility methods for handling requests and responses.
 */
class Endpoint
{
    /** @var string HTTP method of the request. */
    public readonly string $method;

    /**
     * @var string Protocol of the request. Valid values are 'http' or 'https'.
     * @example http, https
     */
    public readonly string $protocol;

    /** @var string Scheme of the request. */
    public readonly string $scheme;

    /** @var string URI of the request. */
    public readonly string $uri;

    /** @var string Path of the request. */
    public readonly string $path;

    /** @var string Query string of the request. */
    public readonly string $query;

    /** @var string|null Body content of the request. */
    public ?string $body = null;

    /** @var array Metadata associated with the request. */
    public array $meta = [];

    /** @var array Custom headers to be added to the response. */
    protected array $customHeaders = [];

    /**
     * @var float start time as UNIX timestamp (in ms)
     * @example 1672774199.520594
     */
    protected readonly float $start;

    /**
     * Endpoint constructor. Initializes properties based on the current request.
     *
     * @param array $params Optional parameters for the endpoint.
     */
    public function __construct(array $params = [])
    {
        $this->start = microtime(true);
        $this->method = htmlspecialchars(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
        $this->protocol = htmlspecialchars(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL'));
        $this->scheme = htmlspecialchars(filter_input(INPUT_SERVER, 'REQUEST_SCHEME'));
        $this->uri = htmlspecialchars(filter_input(INPUT_SERVER, 'REQUEST_URI'));

        $url = parse_url($this->uri);
        $this->path = $url["path"];
        $this->query = $url["query"] ?? "";

        if (in_array($this->method, ["POST", "PUT"])) {
            $this->body = file_get_contents("php://input");
        }

        if (method_exists($this, "setup")) {
            $this->setup($params);
        }
    }

    /**
     * Sends a JSON error response and terminates the script.
     *
     * @param int $errorCode HTTP error code.
     * @param string $msg Error message.
     * @param array $details Additional details about the error.
     * @return never
     */
    protected function throwJsonError(int $errorCode, string $msg, array $details = []): never
    {
        foreach ($this->customHeaders as $customHeader) {
            header($customHeader);
        }
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("$this->protocol $errorCode $msg");
        http_response_code($errorCode);

        print json_encode(array_merge(["status" => $errorCode, "msg" => $msg], $this->meta, $details));

        exit;
    }

    /**
     * Sends a successful JSON response and terminates the script.
     *
     * @param array $data Data to be included in the response.
     * @param string $msg Success message.
     * @return never
     */
    protected function returnJsonResult(array $data, string $msg = 'success'): never
    {
        foreach ($this->customHeaders as $customHeader) {
            header($customHeader);
        }
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("$this->protocol 200 success");

        $queryTime = round(microtime(true) - $this->start, 4);
        print json_encode(array_merge([
            "status" => 200,
            "msg" => $msg,
            "queryTime" => $queryTime
        ], $this->meta, $data));

        exit;
    }

    /**
     * Sends a plain text response and terminates the script.
     *
     * @param string $string Text content to be sent in the response.
     * @param string $msg Success message.
     * @return never
     */
    protected function returnPlainText(string $string, string $msg = 'success'): never
    {
        foreach ($this->customHeaders as $customHeader) {
            header($customHeader);
        }
        header("Content-Type: text/plain");
        header("Access-Control-Allow-Origin: *");
        header("$this->protocol 200 $msg");

        print $string;

        exit;
    }

    /**
     * Sends a response with allowed options and terminates the script.
     *
     * @param array $options Allowed HTTP methods.
     * @return never
     */
    protected function returnOptions(array $options): never
    {
        foreach ($this->customHeaders as $customHeader) {
            header($customHeader);
        }
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: " . implode(', ', $options));
        header("Content-Type: application/json");
        header("$this->protocol 200 success");

        print json_encode(array_merge([
            "status" => 200,
            "query_time" => round(microtime(true) - $this->start, 4)
        ], $this->meta));

        exit;
    }
}
