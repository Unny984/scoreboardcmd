<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use ScoreHud\event\PlayerTagUpdateEvent;

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
        $player = $event->getPlayer();
        $tag = $event->getTag();
        if ($tag === "scorecountdown.timer") {
            $event->setValue($this->plugin->getFormattedTime());
        }
    }
}
