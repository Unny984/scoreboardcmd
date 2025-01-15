<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;

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
        $tag = $event->getScoreTag();

        // Check if the tag is "scorecountdown.timer"
        if ($tag->getName() === "scorecountdown.timer") {
            $tag->setValue($this->plugin->getFormattedTime());
            $event->setScoreTag($tag); // Ensure the tag is updated in the event
        }
    }
}
