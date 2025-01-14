<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    private ?int $timer = null;

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task
        $plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends \pocketmine\scheduler\Task {
            private TimerAddon $addon;

            public function __construct(TimerAddon $addon) {
                $this->addon = $addon;
            }

            public function onRun(): void {
                $this->addon->updateTimer();
            }
        }, 20); // Every second
    }

    public function setTimer(int $time): void {
        $this->plugin->getLogger()->info("Setting global timer to: {$time}");
        $this->timer = $time;
    }

    public function clearTimer(): void {
        $this->plugin->getLogger()->info("Clearing global timer");
        $this->timer = null;
    }

    public function updateTimer(): void {
        if ($this->timer !== null) {
            if ($this->timer > 0) {
                $this->timer--;
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $minutes = intdiv($this->timer, 60);
                    $seconds = $this->timer % 60;

                    $this->plugin->getLogger()->info("Updating timer for player {$player->getName()}: {$minutes}:{$seconds}");

                    // Update tags for each player dynamically
                    $event = new TagsResolveEvent($player, [
                        "scorecountdown.timer" => sprintf("%02d:%02d", $minutes, $seconds),
                    ]);
                    $event->call();
                }
            } else {
                $this->plugin->getLogger()->info("Global timer has ended.");
                $this->clearTimer();
            }
        }
    }
}
