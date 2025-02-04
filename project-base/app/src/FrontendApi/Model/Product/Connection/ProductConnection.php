<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;

class ProductConnection extends Connection
{
    /**
     * @var callable
     */
    private $productFilterOptionsClosure;

    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param \Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface|null $pageInfo
     * @param callable $productFilterOptionsClosure
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     */
    public function __construct(
        array $edges,
        ?PageInfoInterface $pageInfo,
        callable $productFilterOptionsClosure,
        private string $orderingMode,
        private string $defaultOrderingMode,
    ) {
        parent::__construct($edges, $pageInfo);

        $this->productFilterOptionsClosure = $productFilterOptionsClosure;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function getProductFilterOptions(): ProductFilterOptions
    {
        return ($this->productFilterOptionsClosure)();
    }

    /**
     * @return string
     */
    public function getOrderingMode(): string
    {
        return $this->orderingMode;
    }

    /**
     * @return string
     */
    public function getDefaultOrderingMode(): string
    {
        return $this->defaultOrderingMode;
    }
}
