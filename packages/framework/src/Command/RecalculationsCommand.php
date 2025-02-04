<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculationsCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:recalculations';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository $categoryVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator $productPriceRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator $productAvailabilityRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     */
    public function __construct(
        private readonly CategoryVisibilityRepository $categoryVisibilityRepository,
        private readonly ProductHiddenRecalculator $productHiddenRecalculator,
        private readonly ProductPriceRecalculator $productPriceRecalculator,
        private readonly ProductVisibilityFacade $productVisibilityFacade,
        private readonly ProductAvailabilityRecalculator $productAvailabilityRecalculator,
        private readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run all recalculations.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Running recalculations:');
        $output->writeln('<fg=green>Categories visibility.</fg=green>');
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $output->writeln('<fg=green>Products price.</fg=green>');
        $this->productPriceRecalculator->runAllScheduledRecalculations();

        $output->writeln('<fg=green>Products hiding.</fg=green>');
        $this->productHiddenRecalculator->calculateHiddenForAll();

        $output->writeln('<fg=green>Products visibility.</fg=green>');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();

        $output->writeln('<fg=green>Products price again (because of variants).</fg=green>');
        // Main variant is set for recalculations after change of variants visibility.
        $this->productPriceRecalculator->runAllScheduledRecalculations();

        $output->writeln('<fg=green>Products availability.</fg=green>');
        $this->productAvailabilityRecalculator->runAllScheduledRecalculations();

        $output->writeln('<fg=green>Products selling denial.</fg=green>');
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForAll();

        return Command::SUCCESS;
    }
}
