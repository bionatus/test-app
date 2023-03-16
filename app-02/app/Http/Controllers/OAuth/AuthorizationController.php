<?php

namespace app\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response as Psr7Response;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Http\Controllers\AuthorizationController as BaseAuthorizationController;
use Illuminate\Contracts\Routing\ResponseFactory;

class AuthorizationController extends BaseAuthorizationController {
	public function authorize(ServerRequestInterface $psrRequest,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens)
    {
        return $this->withErrorHandling(function () use ($psrRequest, $request, $clients, $tokens) {
    		$user = $request->user();
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);
            $authRequest->setUser(new User($user->getKey()));

			$authRequest->setAuthorizationApproved(true);

			$response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);
			$headers = $response->getHeaders();
    		$location = sprintf('%s&back_url=%s', $headers['Location'][0], $request->query->get('back_url'));

			$response = $response->withHeader('Location', $location);

			return $this->convertResponse($response);
		});
    }
}
