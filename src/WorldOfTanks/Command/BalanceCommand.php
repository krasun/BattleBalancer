<?php

namespace WorldOfTanks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Loader\BattleLoaderInterface;
use WorldOfTanks\BattleBalancer\Loader\Event\LoadedTeamsEvent;
use WorldOfTanks\BattleBalancer\Loader\Events as LoaderEvents;
use WorldOfTanks\BattleBalancer\Loader\LoaderException;
use WorldOfTanks\BattleBalancer\Model\Battle;
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
     * @param ApiClient $apiClient
     * @param EventDispatcherInterface $dispatcher
     * @param string $developerContacts
     */
    public function __construct($name, BattleConfig $defaultBattleConfig, BattleLoaderInterface $battleLoader, Balancer $balancer, ApiClient $apiClient, EventDispatcherInterface $dispatcher, $developerContacts)
    {
        parent::__construct($name);

        $this->defaultBattleConfig = $defaultBattleConfig;
        $this->battleLoader = $battleLoader;
        $this->balancer = $balancer;
        $this->apiClient = $apiClient;
        $this->dispatcher = $dispatcher;
        $this->developerContacts = $developerContacts;
    }

    protected function configure()
    {
        $this
            ->setName('balancer:balance')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->listenEventsAndNotify($output);

        try {
            $output->writeln(sprintf(
                'Start balancing battle for %s players per team with tank level between %s and %s...',
                $this->defaultBattleConfig->getRequiredMemberNumPerTeam(),
                $this->defaultBattleConfig->getMinTankLevel(),
                $this->defaultBattleConfig->getMaxTankLevel()
            ));

            $startTime = microtime(true);

            $battle = $this->battleLoader->load($this->defaultBattleConfig);
            $this->balancer->balance($this->defaultBattleConfig, $battle);

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
        $teamHeaders = ['Id'];
        $teamRows = [
            $this->buildTeamInfoTableRow($battle->getTeamA()->getTeamInfo()),
            $this->buildTeamInfoTableRow($battle->getTeamB()->getTeamInfo())
        ];

        $playerHeaders = ['Player id', 'Team id'];
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
        return [ $teamInfo->getId() ];
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

    private function convert($size)
    {
        $unit = array('', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

        return @ round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
    }

    private function buildPlayerInfoTableRows(Team $team)
    {
        $playerRows = [];
        foreach ($team->getPlayers() as $player) {
            $playerRows[] = [
                $player->getId(),
                $team->getTeamInfo()->getId()
            ];
        }

        return $playerRows;
    }
}