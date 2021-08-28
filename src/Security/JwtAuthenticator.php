<?php
namespace App\Security;

use App\Entity\Usuario;
use App\Repository\ApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContainerBagInterface
     */
    private $params;

    /**
     * @var ApiTokenRepository
     */
    private $apiTokenRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface $params,
        ApiTokenRepository $apiTokenRepository
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    public function start(Request $request, AuthenticationException $authException = null) : Response
    {
        return new JsonResponse(['message' => 'Necessário efetuar o login'], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request) : bool
    {
//        var_dump($request); exit;
        return true;
    }

    public function getCredentials(Request $request)
    {
        $token = $request->headers->get('Authorization');

        if (strpos($token, 'Bearer ') !== 0) {
            throw new AuthenticationException('Requisição inválida');
        }

        return str_replace('Bearer ', '', $token);
    }

    public function getUser(
        $credentials,
        UserProviderInterface $userProvider
    ) : UserInterface {
        try {
            $jwt = (array) JWT::decode(
                $credentials,
                $this->params->get('jwt_secret'),
                ['HS256']
            );

            return $this->entityManager->getRepository(Usuario::class)
                ->findOneBy([
                    'email' => $jwt['email'],
                ]);
        } catch (ExpiredException $exception) {
            try {
                $this->apiTokenRepository->removerToken($credentials);
            } catch (EntityNotFoundException $e) {
            }

            throw new AuthenticationException('Efetuar o login');
        } catch (\Exception $exception) {
            throw new AuthenticationException($exception->getMessage());
        }
    }

    public function checkCredentials($credentials, UserInterface $user) : bool
    {
        $apiToken = $this->apiTokenRepository->findOneBy([
            'usuario_id' => $user->getId(),
            'token' => $credentials
        ]);

        if ($apiToken !== null) {
            return true;
        }

        try {
            $this->apiTokenRepository->removerToken($credentials);
        } catch (EntityNotFoundException $e) {
        }

        throw new AuthenticationException('Token inválido');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : ?Response
    {
        return new JsonResponse(
            ['message' => $exception->getMessage()],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey) : ?Response
    {
        return null;
    }

    public function supportsRememberMe() : bool
    {
        return false;
    }
}
