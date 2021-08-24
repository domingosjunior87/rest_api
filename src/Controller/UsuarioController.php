<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
