<?php

namespace WorldOfTanks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Loader\BattleLoader;
use WorldOfTanks\BattleBalancer\Loader\BattleLoaderInterface;

class BalancerCommand extends Command
{
    /**
     * @var BattleConfig
     */
    private $battleConfig;

    /**
     * @var BattleLoaderInterface
     */
    private $battleLoader;

    /**
     * @var Balancer
     */
    private $balancer;

    /**
     * @param null|string $name
     * @param BattleConfig $battleConfig
     * @param BattleLoaderInterface $battleLoader
     * @param Balancer $balancer
     */
    public function __construct($name, BattleConfig $battleConfig, BattleLoaderInterface $battleLoader, Balancer $balancer)
    {
        parent::__construct($name);

        $this->battleConfig = $battleConfig;
        $this->battleLoader = $battleLoader;
        $this->balancer = $balancer;
    }

    protected function configure()
    {
        $this
            ->setName('balancer:run')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $battle = $this->battleLoader->load($this->battleConfig);
        $balancedBattle = $this->balancer->balance($battle);
    }
} 