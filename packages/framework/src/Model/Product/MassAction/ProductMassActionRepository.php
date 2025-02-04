<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\MassAction;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductMassActionRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @param int[] $selectedProductIds
     * @param bool $hidden
     */
    public function setHidden(array $selectedProductIds, $hidden)
    {
        $updateQueryBuilder = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.hidden', ':value')->setParameter('value', $hidden)
            ->set('p.recalculateVisibility', 'TRUE')
            ->where('p.id IN (:productIds)')->setParameter('productIds', $selectedProductIds);

        $updateQueryBuilder->getQuery()->execute();
    }
}
