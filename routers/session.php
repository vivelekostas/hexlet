<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$users = [
    ['name' => 'kostas', 'passwordDigest' => hash('sha256', 'kostas1988')]
];

$app->post('/session', function (Request $request, Response $response) use ($users) {
    $userData = $request->getParsedBodyParam('user');
    $userData['password'] = hash('sha256', $userData['password']);
    foreach ($users as $user) {
        if (($user['name'] == $userData['name']) && ($user['passwordDigest'] == $userData['password'])) {
            $_SESSION['user'] = $userData;
            return $response->withRedirect('/');            
        }
    }
    $this->get('flash')->addMessage('error', 'Wrong password or name!');
    return $response->withRedirect('/');   
});

$app->delete('/session', function ($request, $response) {
    $_SESSION = [];
    session_destroy();
    return $response->withRedirect('/');
});
