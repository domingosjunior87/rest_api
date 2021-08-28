<?php
namespace App\Controller;

use App\Exception\DataValidationException;
use App\Repository\ApiTokenRepository;
use App\Repository\RecuperarSenhaRepository;
use App\Repository\UsuarioRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(
        Request $request,
        UsuarioRepository $usuarioRepository,
        UserPasswordEncoderInterface $encoder,
        ApiTokenRepository $apiTokenRepository
    ) : JsonResponse {
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
            'exp'  => (new \DateTime())->modify($this->getParameter('jwt_duration'))->getTimestamp()
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'));

        $apiTokenRepository->salvar($usuario->getId(), $jwt);

        return $this->json(['token' => $jwt]);
    }

    /**
     * @Route("/create_user", name="_create_user", methods={"POST"})
     */
    public function create(
        Request $request,
        UsuarioRepository $usuarioRepository
    ) : JsonResponse {
        $dados = $request->request->all();

        try {
            $usuarioRepository->cadastrar(
                $dados['nome'],
                $dados['senha'],
                $dados['email']
            );
        } catch (DataValidationException $e) {
            return $this->json(['mensagem' => $e->getMessage()], 400);
        }

        return $this->json(['cadastrado' => true]);
    }

    /**
     * @Route("/recuperar_senha", name="_recuperar_senha", methods={"POST"})
     */
    public function recuperarSenha(
        Request $request,
        MailerInterface $mailer,
        UsuarioRepository $usuarioRepository,
        RecuperarSenhaRepository $recuperarSenhaRepository
    ): JsonResponse {
        $dados = $request->request->all();

        if (!isset($dados['email'])) {
            return $this->json(['mensagem' => 'Informe o email'], 400);
        }

        $usuario = $usuarioRepository->findOneBy(['email' => $dados['email']]);

        if ($usuario === null) {
            return $this->json(['mensagem' => 'Email não cadastrado'], 404);
        }

        $destinatario = sprintf('%s <%s>', $usuario->getNome(), $usuario->getEmail());
        $destinatario = Address::create($destinatario);

        $codigo = mt_rand(111111, 999999);

        $mensagem = [
            'Para recuperar sua senha, informe o seu e-mail e o código a seguir, em menos de uma hora:',
            $codigo
        ];

        $recuperarSenhaRepository->salvar($codigo, $usuario->getEmail());

        $email = (new Email())
            ->from('domingosjunior87@gmail.com')
            ->to($destinatario)
            ->subject('Recuperação de senha')
            ->text(implode(' ', $mensagem))
            ->html('<p>' . implode('</p><p>', $mensagem) . '</p>');

        $mailer->send($email);

        return $this->json(['mensagem' => 'email enviado']);
    }

    /**
     * @Route("/nova_senha", name="_nova_senha", methods={"POST"})
     */
    public function alterarSenha(
        Request $request,
        UsuarioRepository $usuarioRepository,
        RecuperarSenhaRepository $recuperarSenhaRepository
    ): JsonResponse {
        $dados = $request->request->all();

        $camposEsperados = [
            'email',
            'codigo',
            'senha'
        ];

        foreach ($camposEsperados as $campo) {
            if (!isset($dados[$campo])) {
                return $this->json(['mensagem' => 'Informe o campo ' . $campo], 400);
            }
        }

        $recuperarSenha = $recuperarSenhaRepository->findOneBy([
            'email' => $dados['email'],
            'codigo' => $dados['codigo']
        ]);

        if ($recuperarSenha === null) {
            return $this->json(['mensagem' => 'Email e código não correspondem'], 404);
        }

        $codigoValido = $recuperarSenha->codigoValido();
        $recuperarSenhaRepository->excluir($recuperarSenha);

        if (!$codigoValido) {
            return $this->json(['mensagem' => 'Código expirou, solicite um novo'], 401);
        }

        $usuario = $usuarioRepository->findOneBy(['email' => $dados['email']]);

        if ($usuario === null) {
            return $this->json(['mensagem' => 'Usuário não encontrado'], 404);
        }

        try {
            $usuarioRepository->atualizarSenha($usuario, $dados['senha']);
        } catch (DataValidationException $e) {
            return $this->json(['mensagem' => $e->getMessage()], 400);
        }

        return $this->json(['mensagem' => 'Senha alterada com sucesso']);
    }
}
