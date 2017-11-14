<?php

namespace DddStarterPack\Domain\Model;

use Ramsey\Uuid\Uuid;

abstract class EntityId
{
    protected $id;

    protected function __construct(?string $anId = null)
    {
        $this->verifyInputId($anId);

        $this->id = (string)$anId ?: Uuid::uuid4()->toString();
    }

    public static function create(?string $anId = null)
    {
        return new static($anId);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function equals(IdentifiableDomainObject $entity)
    {
        return $this->id() === $entity->id()->id();
    }

    protected function verifyInputId($anId)
    {
        if (is_object($anId)) {
            throw new \InvalidArgumentException("Entity id input must be scalar type");
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id();
    }
}
