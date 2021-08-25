<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class Controller extends AbstractController
{
    protected function returnJsonResponseError(ConstraintViolationListInterface $errors) : JsonResponse
    {
        $errorsString = [];

        foreach ($errors as $error) {
            $errorsString[] = $error->getMessage();
        }

        return $this->json(['mensagem' => implode(' ', $errorsString)], 400);
    }
}
