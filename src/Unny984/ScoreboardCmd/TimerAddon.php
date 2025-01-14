<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\PlayerScoreTagEvent;
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

        // Schedule a repeating task to update the timers
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
        $this->timers[$player->getName()] = $time;
    }

    public function getTimer(Player $player): ?int {
        return $this->timers[$player->getName()] ?? null;
    }

    public function clearTimer(Player $player): void {
        unset($this->timers[$player->getName()]);
    }

    public function updateTimers(): void {
        foreach ($this->timers as $name => $time) {
            if ($time > 0) {
                $this->plugin->getLogger()->info("Updating timer for player: {$name} to " . ($time - 1));
                $this->timers[$name]--;
    
                // Trigger PlayerScoreTagEvent for the player
                $player = $this->plugin->getServer()->getPlayerExact($name);
                if ($player !== null) {
                    $this->triggerTagsUpdate($player);
                }
            } else {
                $this->plugin->getLogger()->info("Timer for player: {$name} has ended.");
                unset($this->timers[$name]);
            }
        }
    }
    

    public function triggerTagsUpdate(Player $player): void {
        // Get the current timer value or set a default value
        $time = $this->getTimer($player) ?? 0;
        $minutes = intdiv($time, 60);
        $seconds = $time % 60;
        $value = sprintf("%02d:%02d", $minutes, $seconds);
    
        // Create a ScoreTag object
        $tag = new ScoreTag("scorecountdown.timer", $value);
    
        // Pass the ScoreTag object to the PlayerScoreTagEvent
        $event = new PlayerScoreTagEvent($player, $tag);
        $event->call();
    }
}
