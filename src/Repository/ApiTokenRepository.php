<?php
namespace App\Repository;

use App\Document\ApiToken;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class ApiTokenRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function salvar(
        int $usuarioId,
        string $token
    ) : bool {
        try{
            $this->removerTokenDeUsuario($usuarioId);
        } catch (EntityNotFoundException $e) {
        }

        $apiToken = new ApiToken();
        $apiToken->setUsuarioId($usuarioId);
        $apiToken->setToken($token);

        $this->getDocumentManager()->persist($apiToken);
        $this->getDocumentManager()->flush();

        return true;
    }

    public function removerTokenDeUsuario(int $usuarioId) : bool
    {
        $apiToken = $this->findOneBy(['usuario_id' => $usuarioId]);

        if (!$apiToken) {
            throw new EntityNotFoundException('Token não encontrado', 404);
        }

        $this->getDocumentManager()->remove($apiToken);
        $this->getDocumentManager()->flush();

        return true;
    }

    public function removerToken(string $token) : bool
    {
        $apiToken = $this->findOneBy(['token' => $token]);

        if (!$apiToken) {
            throw new EntityNotFoundException('Token não encontrado', 404);
        }

        $this->getDocumentManager()->remove($apiToken);
        $this->getDocumentManager()->flush();

        return true;
    }
}
