<?php

namespace WorldOfTanks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\TankInfo;

class TankInfoCommand extends Command
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    public function __construct($name, ApiClient $apiClient)
    {
        parent::__construct($name);

        $this->apiClient = $apiClient;
    }

    protected function configure()
    {
        $this
            ->setName('balancer:tank-info')
            ->addOption('order-by', 's', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tankInfoHeaders = ['tankId', 'level', 'maxHealth', 'gunDamageMin', 'gunDamageMax'];

        $tankRegistry = $this->apiClient->loadTankTankRegistry();
        $tankInfoRows = [];
        /** @var TankInfo $tankInfo */
        foreach ($tankRegistry->all() as $tankInfo) {
            $row = [];
            foreach ($tankInfoHeaders as $propertyName) {
                $getter = 'get' . ucfirst($propertyName);
                $row[] = method_exists($tankInfo, $getter) ? $tankInfo->$getter() : '';
            }
            $tankInfoRows[] = $row;
        }

        // Render tanks
        $output->writeln('Tanks info');
        $this->renderTable($output, $tankInfoHeaders, $tankInfoRows);

        $summaryRows = [];
        foreach ($tankInfoHeaders as $index => $header) {
            $vals = array_map(function ($tankInfoRow) use ($index) {
                return $tankInfoRow[$index];
            }, $tankInfoRows);
            $summaryRows[] = [
                $header,
                min($vals),
                max($vals),
            ];
        }

        $output->writeln('Summary tanks info');
        $this->renderTable($output, ['Property', 'Min', 'Max'], $summaryRows);
    }

    private function renderTable(OutputInterface $output, array $headers, array $rows)
    {
        $table = $this->getHelper('table');
        $table
            ->setHeaders($headers)
            ->setRows($rows)
        ;
        $table->render($output);
    }
}