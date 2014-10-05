<?php

namespace WorldOfTanks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\TankInfo;
use WorldOfTanks\BattleBalancer\Balance\BalanceWeightCalculatorInterface;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Loader\BattleLoaderInterface;
use WorldOfTanks\BattleBalancer\Loader\Event\LoadedTeamsEvent;
use WorldOfTanks\BattleBalancer\Loader\Events as LoaderEvents;
use WorldOfTanks\BattleBalancer\Loader\LoaderException;
use WorldOfTanks\BattleBalancer\Model\Battle;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;
use WorldOfTanks\BattleBalancer\Model\Team;
use WorldOfTanks\BattleBalancer\Model\TeamInfo;

class BalanceCommand extends Command
{
    /**
     * @var BattleConfig
     */
    private $defaultBattleConfig;

    /**
     * @var BattleLoaderInterface
     */
    private $battleLoader;

    /**
     * @var Balancer
     */
    private $balancer;

    /**
     * @var BalanceWeightCalculatorInterface
     */
    private $balanceWeightCalculator;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $developerContacts;

    /**
     * @param null|string $name
     * @param BattleConfig $defaultBattleConfig
     * @param BattleLoaderInterface $battleLoader
     * @param Balancer $balancer
     * @param BalanceWeightCalculatorInterface $balanceWeightCalculator
     * @param ApiClient $apiClient
     * @param EventDispatcherInterface $dispatcher
     * @param string $developerContacts
     */
    public function __construct(
        $name,
        BattleConfig $defaultBattleConfig,
        BattleLoaderInterface $battleLoader,
        Balancer $balancer,
        BalanceWeightCalculatorInterface $balanceWeightCalculator,
        ApiClient $apiClient,
        EventDispatcherInterface $dispatcher,
        $developerContacts
    )
    {
        parent::__construct($name);

        $this->defaultBattleConfig = $defaultBattleConfig;
        $this->battleLoader = $battleLoader;
        $this->balancer = $balancer;
        $this->balanceWeightCalculator = $balanceWeightCalculator;
        $this->apiClient = $apiClient;
        $this->dispatcher = $dispatcher;
        $this->developerContacts = $developerContacts;
    }

    protected function configure()
    {
        $this
            ->setName('balancer:balance')
            ->addOption('min-tank-level', null, InputOption::VALUE_REQUIRED)
            ->addOption('max-tank-level', null, InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->listenEventsAndNotify($output);

        try {
            $battleConfig = $this->createBattleConfig($input);

            $output->writeln(sprintf(
                'Start balancing battle for %s players per team with tank level between %s and %s...',
                $battleConfig->getRequiredMemberNumPerTeam(),
                $battleConfig->getMinTankLevel(),
                $battleConfig->getMaxTankLevel()
            ));

            $startTime = microtime(true);

            $battle = $this->battleLoader->load($battleConfig);
            $this->balancer->balance($battleConfig, $battle);

            $endTime = microtime(true);

            $this->renderBattle($output, $battle);

            if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('Tech. information:');
                $this->renderTable($output, ['Peek mem. usage', 'Run time (sec.)', 'API request num.'], [[
                    $this->convert(memory_get_peak_usage(true)),
                    ($endTime - $startTime),
                    $this->apiClient->getRequestNum()
                ]]);
            }

            $output->writeln('<info>Good luck!</info>');
        } catch (LoaderException $e) {
            $output->writeln(sprintf(
                '<error>Loader can`t load teams: "%s"! Try again or contact with developer: %s.</error>',
                $e->getMessage(),
                $this->developerContacts
            ));
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Something terrible has happened: "%s"! Try again or contact with developer: %s.</error>',
                $e->getMessage(),
                $this->developerContacts
            ));
        }
    }

    /**
     * @return BattleConfig
     */
    private function createBattleConfig(InputInterface $input)
    {
        $requiredPlayerNumPerTeam = $this->defaultBattleConfig->getRequiredMemberNumPerTeam();
        $minTankLevel = $this->getIntOption($input, 'min-tank-level', $this->defaultBattleConfig->getMinTankLevel());
        $maxTankLevel = $this->getIntOption($input, 'max-tank-level', $this->defaultBattleConfig->getMaxTankLevel());

        if (($minTankLevel < TankInfo::MIN_TANK_LEVEL) or ($minTankLevel > TankInfo::MAX_TANK_LEVEL)
            or ($maxTankLevel < TankInfo::MIN_TANK_LEVEL) or ($maxTankLevel > TankInfo::MAX_TANK_LEVEL)
        ) {
            throw new \InvalidArgumentException(sprintf('Tank level must be between %s and %s',
                TankInfo::MIN_TANK_LEVEL,
                TankInfo::MAX_TANK_LEVEL
            ));
        }

        if ($minTankLevel > $maxTankLevel) {
            throw new \InvalidArgumentException('Max. tank level must be greater or equal to min. tank level');
        }

        return (new BattleConfig())
            ->setRequiredMemberNumPerTeam($requiredPlayerNumPerTeam)
            ->setMinTankLevel($minTankLevel)
            ->setMaxTankLevel($maxTankLevel)
        ;
    }

    private function listenEventsAndNotify(OutputInterface $output)
    {
        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TEAMS, function ($e) use ($output) {
            $output->writeln("Loading teams...");
        });

        $this->dispatcher->addListener(LoaderEvents::TEAMS_LOADED, function (LoadedTeamsEvent $e) use ($output) {
            $output->writeln(sprintf("<info>%s teams successfully loaded.</info>", $e->getTeamNum()));
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TEAM_PLAYERS, function ($e) use ($output) {
            $output->writeln("Loading players...");
        });

        $this->dispatcher->addListener(LoaderEvents::TEAM_PLAYERS_LOADED, function ($e) use ($output) {
            $output->writeln("<info>Players successfully loaded.</info>");
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_PLAYER_TANKS, function ($e) use ($output) {
            $output->writeln("Loading players tanks...");
        });

        $this->dispatcher->addListener(LoaderEvents::PLAYER_TANKS_LOADED, function ($e) use ($output) {
            $output->writeln("<info>Players tanks successfully loaded.</info>");
        });

        $this->dispatcher->addListener(LoaderEvents::BEFORE_LOAD_TANKS, function ($e) use ($output) {
            $output->writeln("Loading tanks...");
        });

        $this->dispatcher->addListener(LoaderEvents::TANKS_LOADED, function ($e) use ($output) {
            $output->writeln("<info>Tanks successfully loaded.</info>");
        });
    }

    private function renderBattle(OutputInterface $output, Battle $battle)
    {
        $teamHeaders = [
            'Id', 'Members', 'Combats', 'Wins', 'Win rate', 'URL'
        ];
        $teamRows = [
            $this->buildTeamInfoTableRow($battle->getTeamA()->getTeamInfo()),
            $this->buildTeamInfoTableRow($battle->getTeamB()->getTeamInfo())
        ];

        $playerHeaders = [
            'Player id',
            'Team id',
            'Tank name',
            'Mastery',
            'Level',
            'Health',
            'Dam. min',
            'Dam. max',
            'Weight',
            'Player URL'
        ];
        $playerRows = array_merge(
            $this->buildPlayerInfoTableRows($battle->getTeamA()),
            $this->buildPlayerInfoTableRows($battle->getTeamB())
        );

        $output->writeln('Selected teams:');
        $this->renderTable($output, $teamHeaders, $teamRows);
        $output->writeln('Selected players:');
        $this->renderTable($output, $playerHeaders, $playerRows);
    }

    private function buildTeamInfoTableRow(TeamInfo $teamInfo)
    {
        return [
            $teamInfo->getId(),
            $teamInfo->getMembersCount(),
            $teamInfo->getCombatsCount(),
            $teamInfo->getWinsCount(),
            // Win rate
            round($teamInfo->getWinsCount() / $teamInfo->getCombatsCount(), 2),
            $this->apiClient->generateClanUrl($teamInfo->getId())
        ];
    }

    private function buildPlayerInfoTableRows(Team $team)
    {
        $playerRows = [];
        foreach ($team->getPlayers() as $player) {
            if (! $player->willPlay()) {
                continue;
            }

            /** @var PlayerTank $selectedTank */
            $selectedTank = null;
            foreach ($player->getTanks() as $playerTank) {
                if ($playerTank->willPlay()) {
                    $selectedTank = $playerTank;
                    break;
                }
            }

            $playerRows[] = [
                $player->getId(),
                $team->getTeamInfo()->getId(),
                $selectedTank->getTank()->getName(),
                $selectedTank->getMarkOfMastery(),
                $selectedTank->getTank()->getLevel(),
                $selectedTank->getTank()->getMaxHealth(),
                $selectedTank->getTank()->getGunDamageMin(),
                $selectedTank->getTank()->getGunDamageMax(),
                $this->balanceWeightCalculator->compute($selectedTank),
                $this->apiClient->generatePlayerUrl($player->getId()),
            ];
        }

        return $playerRows;
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

    private function getIntOption(InputInterface $input, $optionName, $defaultValue)
    {
        if ($option = $input->getOption($optionName)) {
            return (int) $option;
        }

        return $defaultValue;
    }

    private function convert($size)
    {
        $unit = array('', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

        return @ round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
    }
}