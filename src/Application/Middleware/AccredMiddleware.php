<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Exception;
use RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


class JwtMiddleware implements Middleware
{

    /**
     * PSR-3 compliant logger.
     * @var LoggerInterface|null
     */
    private $logger;
    
    /**
     * Last error message.
     * @var string
     */
    private $message;

    private $options = [
        "levelmin" => 0
    ];

    public function __construct(array $options = [])
    {
        /* Store passed in options overwriting any defaults. */
        $this->hydrate($options);

    }

       /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();

        /* If rules say we should not authenticate call next and return. */
        if (false === $this->shouldAuthenticate($request)) {
            return $handler->handle($request);
        }

        /* HTTP allowed only if secure is false or server is in relaxed array. */
        if ("https" !== $scheme && true === $this->options["secure"]) {
            if (!in_array($host, $this->options["relaxed"])) {
                $message = sprintf(
                    "Insecure use of middleware over %s denied by configuration.",
                    strtoupper($scheme)
                );
                throw new RuntimeException($message,101);
            }
        }

        /* If token cannot be found or decoded return with 401 Unauthorized. */
        try {
            $token = $this->fetchToken($request);

            if(null === $token){
                throw new RuntimeException("Not token found",102);
            }

            /* Retrieve user name from Token payload: */
            $userName = $this->getUserToken($token);

            if(null === $userName || $userName == ""){
                throw new RuntimeException("Bad payload",103);
            }






    }

     /**
     * Call the error handler if it exists.
     *
     * @param mixed[] $arguments
     */
    private function processError(Response $response, array $arguments): Response
    {
        if (is_callable($this->options["error"])) {
            $handlerResponse = $this->options["error"]($response, $arguments);
            if ($handlerResponse instanceof Response) {
                return $handlerResponse;
            }
        }
        return $response;
    }

    /**
     * Fetch the access token.
     */
    private function fetchToken(Request $request): string
    {
        /* Check for token in header. */
        $header = $request->getHeaderLine($this->options["header"]);

        if (false === empty($header)) {
            if (preg_match($this->options["regexp"], $header, $matches)) {
                $this->log(LogLevel::DEBUG, "Using token from request header");
                return $matches[1];
            }
        }

        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();

        if (isset($cookieParams[$this->options["cookie"]])) {
            $this->log(LogLevel::DEBUG, "Using token from cookie");
            if (preg_match($this->options["regexp"], $cookieParams[$this->options["cookie"]], $matches)) {
                return $matches[1];
            }
            return $cookieParams[$this->options["cookie"]];
        };

        /* If everything fails log and throw. */
        throw new RuntimeException("Token not found.",106);
    }

    /**
     * Hydrate options from given array.
     *
     * @param mixed[] $data
     */
    private function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace(".", " ", $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                /* Try to use setter */
                /** @phpstan-ignore-next-line */
                call_user_func([$this, $method], $value);
            } else {
                /* Or fallback to setting option directly */
                $this->options[$key] = $value;
            }
        }
    }


}