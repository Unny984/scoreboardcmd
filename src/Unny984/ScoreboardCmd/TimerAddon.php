<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    private array $timers = [];

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register this class as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task to update timers
        $plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends \pocketmine\scheduler\Task {
            private TimerAddon $addon;

            public function __construct(TimerAddon $addon) {
                $this->addon = $addon;
            }

            public function onRun(): void {
                $this->addon->updateTimers();
            }
        }, 20); // Runs every second
    }

    public function setTimer(Player $player, int $time): void {
        $this->plugin->getLogger()->info("Setting timer for player: {$player->getName()} with time: {$time}");
        $this->timers[$player->getName()] = $time;
    }

    public function getTimer(Player $player): ?int {
        return $this->timers[$player->getName()] ?? null;
    }

    public function clearTimer(Player $player): void {
        $this->plugin->getLogger()->info("Clearing timer for player: {$player->getName()}");
        unset($this->timers[$player->getName()]);
    }

    public function updateTimers(): void {
        foreach ($this->timers as $name => $time) {
            if ($time > 0) {
                $this->plugin->getLogger()->info("Updating timer for player: {$name} to " . ($time - 1));
                $this->timers[$name]--;
            } else {
                $this->plugin->getLogger()->info("Timer for player: {$name} has ended.");
                unset($this->timers[$name]);
            }
        }
    }

    public function onTagsResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
    
        if (isset($this->timers[$name])) {
            $time = $this->timers[$name];
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;
    
            $this->plugin->getLogger()->info("Setting timer in TagsResolveEvent: {$minutes}:{$seconds}");
    
            // Set the placeholder using ScoreTag
            $event->setTag(new ScoreTag("scorecountdown.timer", sprintf("%02d:%02d", $minutes, $seconds)));
        } else {
            $this->plugin->getLogger()->info("No active timer for player in TagsResolveEvent: $name");
    
            // Set the placeholder to a default value
            $event->setTag(new ScoreTag("scorecountdown.timer", "00:00"));
        }
    }
}
