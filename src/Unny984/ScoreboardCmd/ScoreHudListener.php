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
    
        // Debugging: Log the tag name and current value
        $this->plugin->getLogger()->info("DEBUG: Updating tag '{$tag->getName()}'");
    
        if ($tag->getName() === "scorecountdown.timer") {
            $value = $this->plugin->getFormattedTime();
    
            // Debugging: Log the value being set
            $this->plugin->getLogger()->info("DEBUG: Setting tag value to '$value'");
    
            $tag->setValue($value); // Update the tag's value
            $event->setTag($tag); // Update the event with the new tag
        }
    }    
}
