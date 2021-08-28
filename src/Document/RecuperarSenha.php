<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class RecuperarSenha
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $email;

    /**
     * @MongoDB\Field(type="integer")
     */
    protected $codigo;

    /**
     * @MongoDB\Field(type="integer")
     */
    protected $expiracao;

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getEmail() : string
    {
        return $this->codigo;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getCodigo() : int
    {
        return $this->codigo;
    }

    public function setCodigo(int $codigo)
    {
        $this->codigo = $codigo;
    }

    public function getExpiracao(): ?\DateTimeInterface
    {
        return \DateTime::createFromFormat('U', $this->expiracao, new \DateTimeZone('America/Manaus'));
    }

    public function setExpiracao(\DateTimeInterface $expiracao)
    {
        $this->expiracao = $expiracao->getTimestamp();
    }

    public function codigoValido() : bool
    {
        $agora = (new \DateTime())->getTimestamp();

        if ($agora <= $this->expiracao) {
            return true;
        }

        return false;
    }
}
