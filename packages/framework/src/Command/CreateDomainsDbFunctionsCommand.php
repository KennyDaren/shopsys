<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\DomainDbFunctionsFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDomainsDbFunctionsCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:domains-db-functions:create';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainDbFunctionsFacade $domainDbFunctionsFacade
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DomainDbFunctionsFacade $domainDbFunctionsFacade,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create new domains DB functions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->wrapInTransaction(function () use ($output) {
            $this->doExecute($output);
        });

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function doExecute(OutputInterface $output)
    {
        $output->writeln('Start of creating db functions.');

        $this->domainDbFunctionsFacade->createDomainDbFunctions();

        $output->writeln('<fg=green>All db functions created.</fg=green>');
    }
}
