<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\scoreboard\ScoreTagManager;
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

        // Schedule a repeating task to decrement the timer
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

    public function setTimer(int $time): void {
        $this->plugin->getLogger()->info("Setting global timer to: {$time}");
        $this->timer = $time;
    }

    public function clearTimer(): void {
        $this->plugin->getLogger()->info("Clearing global timer");
        $this->timer = null;
    }

    public function updateTimer(): void {
        if ($this->timer === null) {
            return;
        }

        if ($this->timer > 0) {
            $this->timer--;

            // Update scoreboard for each online player
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $minutes = intdiv($this->timer, 60);
                $seconds = $this->timer % 60;

                // Debug in console
                $this->plugin->getLogger()->info(
                    "Updating timer for {$player->getName()}: {$minutes}:{$seconds}"
                );

                // Create ScoreTag with ID matching scorehud.yml placeholder
                $scoreTag = new ScoreTag(
                    "scorecountdown.timer",
                    sprintf("%02d:%02d", $minutes, $seconds)
                );

                // Set/update the tag in ScoreTagManager
                ScoreTagManager::setTag($player, $scoreTag);
            }
        } else {
            $this->plugin->getLogger()->info("Global timer has ended.");
            $this->clearTimer();
        }
    }
}
