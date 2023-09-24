<?php

declare(strict_types=1);

#Users:
use App\Application\Actions\User\GenerateTokenAction;
use App\Application\Actions\User\ListUserAction;
use App\Application\Actions\User\NewUserAction;
use App\Application\Actions\User\UpdateUserAction;
use App\Application\Actions\User\DeleteUserAction;

#Tampons:
use App\Application\Actions\Tampon\ListTamponAction;
use App\Application\Actions\Tampon\ViewTamponAction;
use App\Application\Actions\Tampon\NewTamponAction;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Tuupola\Middleware\HttpBasicAuthentication;
use Tuupola\Middleware\HttpBasicAuthentication\PdoAuthenticator;
use App\Application\Middleware\JwtMiddleware;


//Tuupola\Middleware\JwtAuthentication

return function (App $app) {
    $pdo = new PDO($_ENV['PDO_URL'], $_ENV['PDO_USER'], $_ENV['PDO_PASS']);

    //LOGGER
    $logger = new Logger('slim-app');
    $processor = new UidProcessor();
    $logger->pushProcessor($processor);
    $handler = new StreamHandler(isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log', Logger::DEBUG);
    $logger->pushHandler($handler);


    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        //$response->getBody()->write('</br>session:'.print_r($this, true));
        return $response;
    });

    // TOKKEN:
    $app->get('/token', GenerateTokenAction::class)->add(new HttpBasicAuthentication([
        "realm" => "Protected",
        "before" => function ($request, $arguments) {
            return $request->withAttribute("user", $arguments["user"]);
        },
        "authenticator" => new PdoAuthenticator([
            "pdo" => $pdo,
            "table" => "users",
            "user" => "username",
            "hash" => "password"
        ]),
    ]));

    //ME:
    $app->group('/me', function (Group $group) {
        $group->get('', InfosMeAction::class);
        $group->put('', UpdateMeAction::class);
    })->add(new  JwtMiddleware([
        "minlevel"=> 1,
        "logger" => $logger,
        "attribute" => "token",
        "pdo" => $pdo,
        "utable" => "users",
        "ttable" => "tokens",
        "user" => "username",
        "after" => function($request, $arguments){
            if(isset($arguments['UIID']))$request = $request->withAttribute('uiid', $arguments['UIID']);
            return $request;
        }
    ]));


    // USERS:
    $app->group('/users', function (Group $group) {
        $group->get('', ListUserAction::class);
        $group->post('', NewUserAction::class);
        $group->put('', UpdateUserAction::class);
        $group->delete('', DeleteUserAction::class);
    })->add(new  JwtMiddleware([
        "minlevel"=> 2,
        "logger" => $logger,
        "attribute" => "token",
        "pdo" => $pdo,
        "utable" => "users",
        "ttable" => "tokens",
        "user" => "username"
    ]));

    // TAMPONS:
    $app->group('/tampons', function (Group $group) {
        $group->get('', ListTamponAction::class);
        $group->post('new', NewTamponAction::class);
        $group->post('delete', ListTamponAction::class);
        $group->post('stock', ListTamponAction::class);
    })->add(new  JwtMiddleware([
        "minlevel"=> 1,
        "logger" => $logger,
        "attribute" => "token",
        "pdo" => $pdo,
        "utable" => "users",
        "ttable" => "tokens",
        "user" => "username"
    ]));

    

};
