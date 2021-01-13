<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 */
class Employee implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private string $uuid;

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getUsername(): string
    {
        return $this->uuid;
    }

    public function getRoles(): array
    {
        return ['ROLE_EMPLOYEE'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
    }
}
