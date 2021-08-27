<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UsuarioRepository $usuarioRepository, UserPasswordEncoderInterface $encoder)
    {
        $usuario = $usuarioRepository->findOneBy([
            'email' => $request->get('email')
        ]);

        if (!$usuario || !$encoder->isPasswordValid($usuario, $request->get('senha'))) {
            return $this->json([
                'message' => 'Email e senha não conferem.',
            ], 401);
        }

        $payload = [
            'usuario' => $usuario->getUsername(),
            'email' => $usuario->getEmail(),
            'exp'  => (new \DateTime())->modify('+5 minutes')->getTimestamp()
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'));

        // TODO Salvar token no MongoDB

        return $this->json(['token' => $jwt]);
    }
}
