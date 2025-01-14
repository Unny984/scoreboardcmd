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

        // Register this class as an event listener
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        // Schedule a repeating task to update the timer
        $plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends \pocketmine\scheduler\Task {
            private TimerAddon $addon;

            public function __construct(TimerAddon $addon) {
                $this->addon = $addon;
            }

            public function onRun(): void {
                $this->addon->updateTimer();
            }
        }, 20); // Runs every second
    }

    public function setTimer(int $time): void {
        $this->plugin->getLogger()->info("Setting global timer with time: {$time}");
        $this->timer = $time;
    }

    public function clearTimer(): void {
        $this->plugin->getLogger()->info("Clearing global timer");
        $this->timer = null;
    }

    public function updateTimers(): void {
    foreach ($this->timers as $name => $time) {
        if ($time > 0) {
            $this->plugin->getLogger()->info("Updating timer for player: {$name} to " . ($time - 1));
            $this->timers[$name]--;

            // Trigger TagsResolveEvent to update the timer dynamically
            $player = $this->plugin->getServer()->getPlayerExact($name);
            if ($player !== null) {
                $event = new TagsResolveEvent($player);
                $event->call();
            }
        } else {
            $this->plugin->getLogger()->info("Timer for player: {$name} has ended.");
            unset($this->timers[$name]);
        }
    }
}


    public function onTagsResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();

        if ($this->timer !== null) {
            $minutes = intdiv($this->timer, 60);
            $seconds = $this->timer % 60;

            $this->plugin->getLogger()->info("Setting global timer in TagsResolveEvent: {$minutes}:{$seconds}");

            // Set the placeholder to the global timer value
            $event->setTag([
                "scorecountdown.timer" => sprintf("%02d:%02d", $minutes, $seconds)
            ]);
        } else {
            $this->plugin->getLogger()->info("No active global timer in TagsResolveEvent.");
            $event->setTag([
                "scorecountdown.timer" => "00:00"
            ]);
        }
    }
}
