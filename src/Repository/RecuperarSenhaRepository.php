<?php
namespace App\Repository;

use App\Document\RecuperarSenha;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class RecuperarSenhaRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecuperarSenha::class);
    }

    public function salvar(
        int $codigo,
        string $email
    ) : bool {
        $recuperarSenha = new RecuperarSenha();
        $recuperarSenha->setEmail($email);
        $recuperarSenha->setCodigo($codigo);
        $recuperarSenha->setExpiracao((new \DateTime())->modify('+1 hour'));

        $this->getDocumentManager()->persist($recuperarSenha);
        $this->getDocumentManager()->flush();

        return true;
    }

    public function excluir(RecuperarSenha $recuperarSenha) : bool
    {
        $this->getDocumentManager()->remove($recuperarSenha);
        $this->getDocumentManager()->flush();

        return true;
    }
}