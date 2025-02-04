<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;

class AdvertRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdvertRepository()
    {
        return $this->em->getRepository(Advert::class);
    }

    /**
     * @param string $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findById($advertId)
    {
        return $this->getAdvertRepository()->find($advertId);
    }

    /**
     * @param string $positionName
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAdvertByPositionQueryBuilder($positionName, $domainId, $category = null)
    {
        if (AdvertPositionRegistry::isCategoryPosition($positionName) && $category === null) {
            throw new LogicException('Cannot retrieve advert on product list page without setting category.');
        }

        $dateToday = (new DateTimeImmutable())->format('Y-m-d 00:00:00');

        $queryBuilder = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.positionName = :positionName')
            ->andWhere('a.hidden = FALSE')
            ->andWhere('a.domainId = :domainId')
            ->andWhere('a.datetimeVisibleFrom is NULL or a.datetimeVisibleFrom <= :now')
            ->andWhere('a.datetimeVisibleTo is NULL or a.datetimeVisibleTo >= :now')
            ->setParameters([
                'domainId' => $domainId,
                'positionName' => $positionName,
                'now' => $dateToday,
            ]);

        if ($category !== null) {
            $queryBuilder
                ->leftJoin('a.categories', 'c')
                ->andWhere('c IS NULL OR c = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder;
    }

    /**
     * @param string $positionName
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPosition($positionName, $domainId, $category = null)
    {
        $count = $this->getAdvertByPositionQueryBuilder($positionName, $domainId, $category)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult();

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        if ((int)$count === 0) {
            return null;
        }

        return $this->getAdvertByPositionQueryBuilder($positionName, $domainId, $category)
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param int $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function getById($advertId)
    {
        $advert = $this->getAdvertRepository()->find($advertId);

        if ($advert === null) {
            $message = 'Advert with ID ' . $advertId . ' not found';

            throw new AdvertNotFoundException($message);
        }

        return $advert;
    }
}
