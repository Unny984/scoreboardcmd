<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\player\Player;

class ScoreHudListener implements Listener {
    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onTagsResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        $time = $this->plugin->getTimer($player);
        if ($time !== null) {
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;

            $event->setTag(new ScoreTag("scorecountdown.timer", sprintf("%02d:%02d", $minutes, $seconds)));
        } else {
            $event->setTag(new ScoreTag("scorecountdown.timer", "00:00"));
        }
    }
}
