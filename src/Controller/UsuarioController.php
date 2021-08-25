<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function create(Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $dados = $request->request->all();

        $usuario = new Usuario();
        $usuario->setNome($dados['nome']);
        $usuario->setEmail($dados['email']);

        try {
            $usuario->setSenha($dados['senha'], $dados['confirmar_senha']);
        } catch (InvalidArgumentException $ex) {
            return $this->json(['mensagem' => $ex->getMessage()], 400);
        }

        $usuario->setCreatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')));
        $usuario->setUpdatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')));

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
    public function update(Usuario $usuario, Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $dados = $request->request->all();

        $usuario->setNome($dados['nome']);
        $usuario->setEmail($dados['email']);
        $usuario->setUpdatedAt(new \DateTime('now', new \DateTimeZone('America/Manaus')));

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
    public function delete(Usuario $usuario) : JsonResponse
    {
        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->remove($usuario);
        $doctrine->flush();

        return $this->json(['removed' => true]);
    }
}
