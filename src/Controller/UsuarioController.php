<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/usuario", name="usuario")
 */
class UsuarioController extends Controller
{
    /**
     * @Route("/", name="_get_all", methods={"GET"})
     */
    public function index(UsuarioRepository $usuarioRepository): JsonResponse
    {
        return $this->json($usuarioRepository->findAll());
    }

    /**
     * @Route("/", name="_create", methods={"POST"})
     */
    public function create(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder
    ) : JsonResponse {
        $dados = $request->request->all();

        $usuario = new Usuario();
        $usuario
            ->setNome($dados['nome'])
            ->setSenha($encoder->encodePassword($usuario, $dados['senha']))
            ->setEmail($dados['email'])
            ->setCreatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')))
            ->setUpdatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')));

        $erros = $validator->validate($usuario);
        if (count($erros) > 0) {
            return $this->returnJsonResponseError($erros);
        }

        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->persist($usuario);
        $doctrine->flush();

        return $this->json(['usuario' => $usuario]);
    }

    /**
     * @Route("/{id}", name="_update", methods={"PUT"})
     */
    public function update(int $id, Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            return $this->json(['mensagem' => 'Usuário não encontrado'], 404);
        }

        $dados = $request->request->all();

        $usuario
            ->setNome($dados['nome'])
            ->setEmail($dados['email'])
            ->setUpdatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')));

        $erros = $validator->validate($usuario);
        if (count($erros) > 0) {
            return $this->returnJsonResponseError($erros);
        }

        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->flush();

        return $this->json($usuario);
    }

    /**
     * @Route("/{id}", name="_delete", methods={"DELETE"})
     */
    public function delete(int $id) : JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            return $this->json(['mensagem' => 'Usuário não encontrado'], 404);
        }

        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->remove($usuario);
        $doctrine->flush();

        return $this->json(['removed' => true]);
    }
}
