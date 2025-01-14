<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    private ?int $timer = null;

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task
        $plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private TimerAddon $addon;

            public function __construct(TimerAddon $addon) {
                $this->addon = $addon;
            }

            public function onRun(): void {
                $this->addon->updateTimer();
            }
        }, 20); // Runs every 20 ticks (1 second)
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

                    // Create the ScoreTag object with 2 parameters (identifier + value)
                    $scoreTag = new ScoreTag(
                        "scorecountdown.timer",           // Tag identifier
                        sprintf("%02d:%02d", $minutes, $seconds) // Actual display value
                    );

                    // Send it via the TagsResolveEvent
                    $event = new TagsResolveEvent($player, [$scoreTag]);
                    $event->call();
                }
            } else {
                $this->plugin->getLogger()->info("Global timer has ended.");
                $this->clearTimer();
            }
        }
    }
}
