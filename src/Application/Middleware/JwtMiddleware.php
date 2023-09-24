<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Exception;
use RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tuupola\Http\Factory\ResponseFactory;

use App\Domain\User\User;
use App\Domain\Token\Token;

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


    /**
     * Stores all the options passed to the middleware.
     *
     * @var array{
     *   secret?: string|array<string>,
     *   secure: bool,
     *   relaxed: array<string>,
     *   algorithm: array<string>,
     *   header: string,
     *   regexp: string,
     *   cookie: string,
     *   attribute: string,
     *   path: array<string>,
     *   ignore: array<string>,
     *   before: null|callable,
     *   after: null|callable,
     *   error: null|callable,
     * }
     */
    private $options = [
        "secure" => true,
        "relaxed" => ["localhost", "127.0.0.1"],
        "algorithm" => ["HS256", "HS512", "HS384"],
        "header" => "Authorization",
        "regexp" => "/Bearer\s+(.*)$/i",
        "cookie" => "token",
        "attribute" => "token",
        "level" => "userlevel",
        "utable" => "users",
        "ttable" => "tokens",
        "username" => "username",
        "userid" => "uiid",
        "path" => ["/"],
        "ignore" => [],
        "before" => null,
        "after" => null
    ];
    
    private $userLevel = 0;

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
        $userUIID = "";
       

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

            /* Retrieve user from DB: */
            $user = $this->getDbUser($userName);

            if(null === $user){ 
                throw new RuntimeException("User not found",104);
            }

            $userUIID = $user->getId();

            /*check if level set*/
            if(isset($this->options["minlevel"])){
                $minlevel = intval($this->options["minlevel"]);
                $userlevel = intval($user->getLevel());
                if($userlevel<$minlevel){
                    throw new RuntimeException("few level",105);
                }
            }

            /* Retrieve token from DB: */
            $dbTokenObj = $this->getDbToken($userUIID);

            if(null === $dbTokenObj){ 
                throw new RuntimeException("Token not found",106);
            }

            /*Token validity test: */
            $expiration = $dbTokenObj->getExpiration();
            $now = time();

            if($expiration < $now ){
                throw new RuntimeException("Token expired",107);
            }

            $dbTokenHash = $dbTokenObj->getToken();

            $decoded = $this->decodeToken($token, $dbTokenHash);
           
        }catch(Exception $exception) {
            $this->log(LogLevel::WARNING, "ERROR:".$exception->getMessage());
            $response = (new ResponseFactory)->createResponse(401);
            $data["status"] = "error";
            $data["message"] = $exception->getMessage();
            $data["code"] = $exception->getCode();
            $response->getBody()->write(
                json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );
            return $response->withHeader("Content-Type", "application/json");
        }

        $params = [
            "decoded" => $decoded,
            "token" => $token,
            "UIID" => $userUIID,
        ];

        /* Add decoded token to request as attribute when requested. */
        if ($this->options["attribute"]) {
            $request = $request->withAttribute($this->options["attribute"], $decoded);
        }

        /* Modify $request before calling next middleware. */
        if (is_callable($this->options["before"])) {
            $beforeRequest = $this->options["before"]($request, $params);
            if ($beforeRequest instanceof Request) {
                $request = $beforeRequest;
            }
        }

        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);



        /* Modify $response before returning. */
        if (is_callable($this->options["after"])) {
            $afterResponse = $this->options["after"]($response, $params);
            if ($afterResponse instanceof Response) {
                return $afterResponse;
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
        throw new RuntimeException("Token not found.",110);
    }

    /**
     * Decode the token.
     *
     * @return mixed[]
     */
    private function decodeToken(string $reqToken, string $dbTokenHash): array
    {
        try {
            $decoded = JWT::decode(
                $reqToken,
                $dbTokenHash,
                (array) $this->options["algorithm"]
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            throw new RuntimeException('Bad token',120);
        }
    }

    private function getUserToken(string $token):string
    {
        $tks = \explode('.', $token);
        if (\count($tks) !== 3) {
            throw new RuntimeException('Wrong number of segments',130);
        }

        list($headb64, $bodyb64, $cryptob64) = $tks;
        
        $payloadRaw = JWT::urlsafeB64Decode($bodyb64);
        if (null === ($payload = JWT::jsonDecode($payloadRaw))) {
            throw new RuntimeException('Invalid claims encoding',131);
        }

        if (\is_array($payload)) {
            // prevent PHP Fatal Error in edge-cases when payload is empty array
            $payload = (object) $payload;
        }

        /*if (!$payload instanceof stdClass) {
            $this->log(LogLevel::DEBUG, "Payload must be a JSON object");
            throw new RuntimeException('Payload must be a JSON object');
        }*/

        if (!isset($payload->Username)) {
            throw new RuntimeException('No username',132);
        }

        return $payload->Username;
    }

    private function getDbUser(string $userName):User
    {
        /*Retrive UIID from user name: */
        $usql = $this->userRequest();
        $statement = $this->options["pdo"]->prepare($usql);
        $statement->execute([$userName]);

        if ($user = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $userObj = new User($user["id"], $user["username"], $user["password"], $user["firstname"], $user["lastname"], $user["email"], $user["uidkey"], $user["level"]);
            return $userObj;
        }else{
            return null;
        }

    }

    private function getDbToken(string $userUIID):Token
    {
        /*Retrive token from UIID: */
        $tsql = $this->tokenRequest();
        $statement = $this->options["pdo"]->prepare($tsql);
        $statement->execute([$userUIID]);

        if ($token = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $tokenObj = new Token($token["id"], $token["uiid"], $token["token"], $token["created"], $token["expiration"]);
            return $tokenObj ;
        }

        return null;
    }

    public function userRequest(): string
    {
        $driver = $this->options["pdo"]->getAttribute(\PDO::ATTR_DRIVER_NAME);

        /* Workaround to test without sqlsrv with Travis */
        if (defined("__PHPUNIT_ATTR_DRIVER_NAME__")) {
            $driver = __PHPUNIT_ATTR_DRIVER_NAME__;
        }

        if ("sqlsrv" === $driver) {
            $sql =
                "SELECT TOP 1 *
                 FROM {$this->options['utable']}
                 WHERE {$this->options['username']} = ?";
        } else {
            $sql =
                "SELECT *
                 FROM {$this->options['utable']}
                 WHERE {$this->options['username']} = ?
                 LIMIT 1";
        }

        return (string) preg_replace("!\s+!", " ", $sql);
    }


    private function tokenRequest(): string
    {
        $driver = $this->options["pdo"]->getAttribute(\PDO::ATTR_DRIVER_NAME);

        /* Workaround to test without sqlsrv with Travis */
        if (defined("__PHPUNIT_ATTR_DRIVER_NAME__")) {
            $driver = __PHPUNIT_ATTR_DRIVER_NAME__;
        }

        if ("sqlsrv" === $driver) {
            $sql =
                "SELECT TOP 1 *
                 FROM {$this->options['ttable']}
                 WHERE {$this->options['userid']} = ?";
        } else {
            $sql =
                "SELECT *
                 FROM {$this->options['ttable']}
                 WHERE {$this->options['userid']} = ?
                 LIMIT 1";
        }

        return (string) preg_replace("!\s+!", " ", $sql);
    }

     /**
     * Check if middleware should authenticate.
     */
    private function shouldAuthenticate(Request $request): bool
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);
    
        /* If request path is matches ignore should not authenticate. */
        foreach ((array)$this->options["ignore"] as $ignore) {
            $ignore = rtrim($ignore, "/");
            if (!!preg_match("@^{$ignore}(/.*)?$@", (string) $uri)) {
                return false;
            }
        }
    
        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", (string) $uri)) {
                return true;
            }
        }
        return false;
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

    /**
     * Set path where middleware should bind to.
     *
     * @param string|string[] $path
     */
    private function path($path): void
    {
        $this->options["path"] = (array) $path;
    }

    /**
     * Set path which middleware ignores.
     *
     * @param string|string[] $ignore
     */
    private function ignore($ignore): void
    {
        $this->options["ignore"] = (array) $ignore;
    }

    /**
     * Set the cookie name where to search the token from.
     */
    private function cookie(string $cookie): void
    {
        $this->options["cookie"] = $cookie;
    }

    /**
     * Set the secure flag.
     */
    private function secure(bool $secure): void
    {
        $this->options["secure"] = $secure;
    }

    /**
     * Set the error handler.
     */
    private function error(callable $error): void
    {
        if ($error instanceof Closure) {
            $this->options["error"] = $error->bindTo($this);
        } else {
            $this->options["error"] = $error;
        }
    }

    /**
     * Set the logger.
     */
    private function logger(LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed[] $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Set the attribute name used to attach decoded token to request.
     */
    private function attribute(string $attribute): void
    {
        $this->options["attribute"] = $attribute;
    }

    /**
     * Set the header where token is searched from.
     */
    private function header(string $header): void
    {
        $this->options["header"] = $header;
    }

    /**
     * Set the regexp used to extract token from header or environment.
     */
    private function regexp(string $regexp): void
    {
        $this->options["regexp"] = $regexp;
    }

    /**
     * Set the allowed algorithms
     *
     * @param string|string[] $algorithm
     */
    private function algorithm($algorithm): void
    {
        $this->options["algorithm"] = (array) $algorithm;
    }

    /**
     * Set the before handler.
     */
    private function before(callable $before): void
    {
        if ($before instanceof Closure) {
            $this->options["before"] = $before->bindTo($this);
        } else {
            $this->options["before"] = $before;
        }
    }

    /**
     * Set the after handler.
     */
    private function after(callable $after): void
    {
        if ($after instanceof Closure) {
            $this->options["after"] = $after->bindTo($this);
        } else {
            $this->options["after"] = $after;
        }
    }

}
