<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class TimerAddon implements Listener {

    private PluginBase $plugin; // Store the plugin instance
    private array $timers = [];

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register this class as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function setTimer(Player $player, int $time): void {
        $this->timers[$player->getName()] = $time;
    }

    public function getTimer(Player $player): ?int {
        return $this->timers[$player->getName()] ?? null;
    }

    public function clearTimer(Player $player): void {
        unset($this->timers[$player->getName()]);
    }

    public function onTagsResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (isset($this->timers[$name])) {
            $time = $this->timers[$name];
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;

            $event->setTag(["scorecountdown.timer" => sprintf("%02d:%02d", $minutes, $seconds)]);
        } else {
            $event->setTag(["scorecountdown.timer" => "00:00"]);
        }
    }
}
