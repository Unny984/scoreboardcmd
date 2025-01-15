<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use ScoreHud\event\PlayerTagUpdateEvent; // Ensure this is the correct namespace

class ScoreHudListener implements Listener
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $player->sendMessage("Scoreboard countdown enabled!");
    }

    public function onTagUpdate(PlayerTagUpdateEvent $event): void
    {
        $tag = $event->getTag(); // Ensure `getTag` exists in the ScoreHud API
        if ($tag === "scorecountdown.timer") {
            $event->setValue($this->plugin->getFormattedTime()); // Ensure `setValue` exists in the ScoreHud API
        }
    }
}
