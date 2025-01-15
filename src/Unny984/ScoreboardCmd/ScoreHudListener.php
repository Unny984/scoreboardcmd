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
        $tag = $event->getTag(); // Retrieve the ScoreTag object
    
        // Check if the tag's name matches "scorecountdown.timer"
        if ($tag->getName() === "scorecountdown.timer") {
            $value = $this->plugin->getFormattedTime();
            $tag->setValue($value); // Update the tag's value
            $event->setTag($tag); // Set the updated tag in the event
    
            // Send debug messages
            $event->getPlayer()->sendMessage("DEBUG: Scoreboard timer updated to $value");
            $event->getPlayer()->sendActionBarMessage("剩余时间: $value");
        }
    }
    
}
