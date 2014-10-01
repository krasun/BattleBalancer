<?php

namespace WorldOfTanks\BattleBalancer\Loader;

final class Events
{
    const BEFORE_LOAD_TEAMS = 'loader.before_load_teams';

    const TEAMS_LOADED = 'loader.teams_loaded';

    const BEFORE_LOAD_TEAM_PLAYERS = 'loader.before_load_team_players';

    const TEAM_PLAYERS_LOADED = 'loader.team_players_loaded';

    const BEFORE_LOAD_PLAYER_TANKS = 'loader.before_load_player_tanks';

    const PLAYER_TANKS_LOADED = 'loader.player_tanks_loaded';

    const BEFORE_LOAD_TANKS = 'loader.before_load_tanks';

    const TANKS_LOADED = 'loader.tanks_loaded';
} 