<?php

namespace WorldOfTanks\BattleBalancer\Loader;

use WorldOfTanks\BattleBalancer\Model\Battle;

interface BattleLoaderInterface
{
    /**
     * Loads battle information.
     *
     * @param BattleConfig $battleConfig
     *
     * @return Battle
     */
    function load(BattleConfig $battleConfig);
} 