<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Exception\DataValidationException;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    public function __construct(
        ManagerRegistry $registry,
        UserPasswordEncoderInterface $encoder,
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
        $this->encoder = $encoder;

        parent::__construct($registry, Usuario::class);
    }

    /**
     * @throws DataValidationException
     */
    public function cadastrar(
        string $nome,
        string $senha,
        string $email
    ) : bool {
        $usuario = new Usuario();

        $usuario
            ->setNome($nome)
            ->setSenha($this->encoder->encodePassword($usuario, $senha))
            ->setEmail($email)
            ->setCreatedAt(new DateTime('now', new DateTimeZone('America/Manaus')))
            ->setUpdatedAt(new DateTime('now', new DateTimeZone('America/Manaus')));

        $erros = $this->validator->validate($usuario);

        if (count($erros) > 0) {
            throw new DataValidationException($erros);
        }

        $doctrine = $this->getEntityManager();
        $doctrine->persist($usuario);
        $doctrine->flush();

        return true;
    }

    /**
     * @throws EntityNotFoundException
     * @throws DataValidationException
     */
    public function atualizar(
        int $id,
        string $nome,
        string $email
    ) : bool {
        $usuario = $this->find($id);

        if (!$usuario) {
            throw new EntityNotFoundException('Usuário não encontrado', 404);
        }

        $usuario
            ->setNome($nome)
            ->setEmail($email)
            ->setUpdatedAt(new DateTime('now', new DateTimeZone('America/Manaus')));

        $erros = $this->validator->validate($usuario);

        if (count($erros) > 0) {
            throw new DataValidationException($erros);
        }

        $doctrine = $this->getEntityManager();
        $doctrine->flush();

        return true;
    }

    /**
     * @throws DataValidationException
     */
    public function atualizarSenha(
        Usuario $usuario,
        string $senha
    ) : bool {
        $usuario
            ->setSenha($this->encoder->encodePassword($usuario, $senha))
            ->setUpdatedAt(new DateTime('now', new DateTimeZone('America/Manaus')));

        $erros = $this->validator->validate($usuario);

        if (count($erros) > 0) {
            throw new DataValidationException($erros);
        }

        $doctrine = $this->getEntityManager();
        $doctrine->flush();

        return true;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function excluir(int $id) : bool
    {
        $usuario = $this->find($id);

        if (!$usuario) {
            throw new EntityNotFoundException('Usuário não encontrado', 404);
        }

        $doctrine = $this->getEntityManager();
        $doctrine->remove($usuario);
        $doctrine->flush();

        return true;
    }
}
