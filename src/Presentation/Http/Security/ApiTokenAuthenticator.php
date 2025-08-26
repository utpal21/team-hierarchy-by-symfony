<?php
declare(strict_types=1);

namespace App\Presentation\Http\Security;

use App\Application\Api\ApiResponder;
use App\Domain\Security\ApiUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ApiTokenAuthenticator extends AbstractAuthenticator
{
    /** 
     * @param string $expectedToken The expected API token for authentication
     * @param ApiResponder $responder The API responder for generating responses
     */
    public function __construct(
        private readonly string $expectedToken,
        private readonly ApiResponder $responder,
    ) {}
    
    /**
     * Determines if the authenticator should be used for the given request.
     *
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/api');
    }

    /**
     * @param Request $request
     * @return Passport
     * @throws AuthenticationException
     */
    public function authenticate(Request $request): Passport
    {
        $providedToken = $request->headers->get('X-API-TOKEN');

        if (!$providedToken) {
            throw new AuthenticationException('No API token provided');
        }

        if ($providedToken !== $this->expectedToken) {
            throw new AuthenticationException('Invalid API token');
        }

        return new SelfValidatingPassport(
            new UserBadge('api-user', fn() => new ApiUser())
        );
    }

    /**
     * Successful authentication handler
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * Failed authentication handler
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->responder->error(
            message: $exception->getMessage(),
            status: Response::HTTP_UNAUTHORIZED,
            errors: null,
            code: Response::HTTP_UNAUTHORIZED        );
    }

    /**
     * Called when authentication is needed, but the user isn't authenticated yet.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->responder->error(
            message: 'Authentication required',
            status: Response::HTTP_UNAUTHORIZED,
            errors: null,
            code: Response::HTTP_UNAUTHORIZED
        );
    }
}
