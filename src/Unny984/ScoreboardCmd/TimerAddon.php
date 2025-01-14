<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    private ?int $timer = null;

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;

        // Register this class as an event listener for ScoreHud to pick up the TagsResolveEvent
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task (runs every 20 ticks = 1 second)
        $plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private TimerAddon $addon;

            public function __construct(TimerAddon $addon) {
                $this->addon = $addon;
            }

            public function onRun(): void {
                $this->addon->updateTimer();
            }
        }, 20);
    }

    /**
     * Set a global timer in seconds.
     * Example: setTimer(100) = 100 seconds countdown
     */
    public function setTimer(int $time): void {
        $this->plugin->getLogger()->info("Setting global timer to: {$time} seconds");
        $this->timer = $time;
    }

    /**
     * Clear the current global timer.
     */
    public function clearTimer(): void {
        $this->plugin->getLogger()->info("Clearing global timer");
        $this->timer = null;
    }

    /**
     * Decrement the timer and update all players' scoreboards every second.
     */
    public function updateTimer(): void {
        // If there's no active timer, do nothing
        if ($this->timer === null) {
            return;
        }

        // If the timer is still above 0, decrement and update scoreboard
        if ($this->timer > 0) {
            $this->timer--;

            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $minutes = intdiv($this->timer, 60);
                $seconds = $this->timer % 60;

                // Debug info in console
                $this->plugin->getLogger()->info(
                    "Updating timer for {$player->getName()}: {$minutes}:{$seconds}"
                );

                // Create a single ScoreTag (placeholder ID matches your scorehud.yml)
                $scoreTag = new ScoreTag(
                    "scorecountdown.timer",             // e.g. {scorecountdown.timer}
                    sprintf("%02d:%02d", $minutes, $seconds) // "01:39", "00:05", etc.
                );

                // Dispatch TagsResolveEvent with one ScoreTag
                $event = new TagsResolveEvent($player, $scoreTag);
                $event->call();
            }
        } else {
            // Timer is 0, so end the countdown
            $this->plugin->getLogger()->info("Global timer has ended.");
            $this->clearTimer();
        }
    }
}
