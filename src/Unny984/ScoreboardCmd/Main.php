<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Ifera\ScoreHud\event\TagsResolveEvent;

class Main extends PluginBase {
    private array $timers = [];

    protected function onEnable(): void {
        // Register ScoreHudListener
        $this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this);

        // Schedule a task to update timers
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private Main $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                $this->plugin->updateTimers();
            }
        }, 20); // Runs every second
    }

    public function setTimer(Player $player, int $time): void {
        $this->timers[$player->getName()] = $time;
    }

    public function clearTimer(Player $player): void {
        unset($this->timers[$player->getName()]);
    }

    public function getTimer(Player $player): ?int {
        return $this->timers[$player->getName()] ?? null;
    }

    public function updateTimers(): void {
        foreach ($this->timers as $name => $time) {
            if ($time > 0) {
                $this->timers[$name]--;

                // Notify ScoreHud to refresh
                $player = $this->getServer()->getPlayerExact($name);
                if ($player !== null && $player->isOnline()) {
                    $event = new TagsResolveEvent($player);
                    $this->getServer()->getPluginManager()->callEvent($event);
                }
            } else {
                unset($this->timers[$name]);
            }
        }
    }
}
