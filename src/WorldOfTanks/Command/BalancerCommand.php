<?php

namespace WorldOfTanks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Loader\BattleLoader;
use WorldOfTanks\BattleBalancer\Loader\BattleLoaderInterface;
use WorldOfTanks\BattleBalancer\Loader\Events as LoaderEvents;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param null|string $name
     * @param BattleConfig $battleConfig
     * @param BattleLoaderInterface $battleLoader
     * @param Balancer $balancer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct($name, BattleConfig $battleConfig, BattleLoaderInterface $battleLoader, Balancer $balancer, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($name);

        $this->battleConfig = $battleConfig;
        $this->battleLoader = $battleLoader;
        $this->balancer = $balancer;
        $this->dispatcher = $dispatcher;
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
        $this->listenLoader($output);

        $battle = $this->battleLoader->load($this->battleConfig);
        $this->balancer->balance($battle);
    }

    private function listenLoader(OutputInterface $output)
    {
        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TEAMS, function ($e) use ($output) {
            $output->writeln('Loading teams...');
        });

        $this->dispatcher->addListener(LoaderEvents::TEAMS_LOADED, function ($e) use ($output) {
            $output->writeln('<info>Teams successfully loaded.</info>');
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TEAM_PLAYERS, function ($e) use ($output) {
            $output->writeln('Loading players...');
        });

        $this->dispatcher->addListener(LoaderEvents::TEAM_PLAYERS_LOADED, function ($e) use ($output) {
            $output->writeln('<info>Players successfully loaded.</info>');
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_PLAYER_TANKS, function ($e) use ($output) {
            $output->writeln('Loading players tanks...');
        });

        $this->dispatcher->addListener(LoaderEvents::PLAYER_TANKS_LOADED, function ($e) use ($output) {
            $output->writeln('<info>Players tanks successfully loaded.</info>');
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TANKS, function ($e) use ($output) {
            $output->writeln('Loading tanks...');
        });

        $this->dispatcher->addListener(LoaderEvents::TANKS_LOADED, function ($e) use ($output) {
            $output->writeln('<info>Tanks successfully loaded.</info>');
        });
    }
} 