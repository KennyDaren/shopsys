<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class UnitFactory implements UnitFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function create(UnitData $data): Unit
    {
        $classData = $this->entityNameResolver->resolve(Unit::class);

        return new $classData($data);
    }
}
