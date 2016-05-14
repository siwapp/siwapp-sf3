<?php

namespace Siwapp\RecurringInvoiceBundle\Command;

use Siwapp\RecurringInvoiceBundle\InvoiceGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePendingCommand extends Command
{
    protected $generator;

    public function __construct(InvoiceGenerator $generator)
    {
        $this->generator = $generator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('siwapp:recurring:generate-pending')
            ->setDescription('Generate pending invoices')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->generator->generateAll();
        $output->writeln(sprintf('Generated %d invoices', $count));
    }
}
