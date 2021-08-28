<?php

namespace App\Controller;

use App\Exception\DataValidationException;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/usuario", name="usuario")
 */
class UsuarioController extends AbstractController
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
     * @Route("/{id}", name="_update", methods={"PUT"})
     */
    public function update(int $id, Request $request, UsuarioRepository $usuarioRepository) : JsonResponse
    {
        $dados = $request->request->all();

        try {
            $usuarioRepository->atualizar(
                $id,
                $dados['nome'],
                $dados['email']
            );
        } catch (DataValidationException $e) {
            return $this->json(['mensagem' => $e->getMessage()], 400);
        } catch (EntityNotFoundException $e) {
            return $this->json(['mensagem' => $e->getMessage()], 404);
        }

        return $this->json(['atualizado' => true]);
    }

    /**
     * @Route("/{id}", name="_delete", methods={"DELETE"})
     */
    public function delete(int $id, UsuarioRepository $usuarioRepository) : JsonResponse
    {
        try {
            $usuarioRepository->excluir($id);
        } catch (EntityNotFoundException $e) {
            return $this->json(['mensagem' => $e->getMessage()], 404);
        }

        return $this->json(['removed' => true]);
    }
}
