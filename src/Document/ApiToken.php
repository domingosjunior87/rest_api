<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ApiToken
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="integer")
     */
    protected $usuario_id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $token;

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getUsuarioId() : ?int
    {
        return $this->usuario_id;
    }

    public function setUsuarioId(int $usuarioId)
    {
        $this->usuario_id = $usuarioId;
    }

    public function getToken() : ?string
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }
}
