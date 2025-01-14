<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag; // <-- IMPORTANT: import this class
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    private ?int $timer = null;

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task every second (20 ticks)
        $plugin->getScheduler()->scheduleRepeatingTask(
            new class($this) extends Task {
                private TimerAddon $addon;
                public function __construct(TimerAddon $addon) {
                    $this->addon = $addon;
                }
                public function onRun(): void {
                    $this->addon->updateTimer();
                }
            },
            20
        );
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

                    $this->plugin->getLogger()->info(
                        "Updating timer for player {$player->getName()}: {$minutes}:{$seconds}"
                    );

                    // Create a ScoreTag for this countdown
                    $scoreTag = new ScoreTag(
                        "scorecountdown.timer",                  // Tag identifier (used in scorehud.yml)
                        sprintf("%02d:%02d", $minutes, $seconds), // The displayed value
                        "scorecountdown.timer"                   // A unique key
                    );

                    // Create and call the event
                    $event = new TagsResolveEvent($player, [$scoreTag]);
                    $event->call();
                }
            } else {
                // Timer reached zero
                $this->plugin->getLogger()->info("Global timer has ended.");
                $this->clearTimer();
            }
        }
    }
}
