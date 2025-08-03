<?php

namespace App\Entity\Security;

use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use App\Repository\PasswordResetCodeRepository;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: PasswordResetCodeRepository::class)]
#[ORM\Index(columns: ["code"], name: "code_idx")]
#[ORM\UniqueConstraint(name: "user_code_uidx", columns: ["user_id", "code"])]
class PasswordResetCode
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 64, unique: true)]
    private ?string $code;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created', type: Types::DATE_MUTABLE)]
    private ?\DateTime $created;

    public function __construct(User $user, string $code = null)
    {
        $this->user = $user;
        $this->created = new DateTime;

        if ($code == null) {
            $code = Uuid::uuid4()->toString();
        }
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getUser(): User
    {
        return $this->user;
    }

    public function __toString()
    {
        return $this->getCode();
    }

}