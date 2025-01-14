<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\event\PlayerScoreTagEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    protected array $timers = [];

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function setTimer(string $playerName, int $time): void {
        $this->timers[$playerName] = $time;
    }

    public function getTimer(string $playerName): ?int {
        return $this->timers[$playerName] ?? null;
    }

    public function clearTimer(string $playerName): void {
        unset($this->timers[$playerName]);
    }

    public function startTimer(Player $player, int $time): void {
        $name = $player->getName();
        $this->setTimer($name, $time);

        $this->plugin->getScheduler()->scheduleRepeatingTask(new class($this, $player) extends Task {
            private TimerAddon $addon;
            private Player $player;

            public function __construct(TimerAddon $addon, Player $player) {
                $this->addon = $addon;
                $this->player = $player;
            }

            public function onRun(): void {
                $name = $this->player->getName();
                $time = $this->addon->getTimer($name);
                if ($time === null) {
                    return;
                }

                $this->addon->setTimer($name, $time - 1);
                if ($time <= 1) {
                    $this->addon->stopTimer($this->player);
                }
            }
        }, 20);
    }

    public function stopTimer(Player $player): void {
        $this->clearTimer($player->getName());
    }

    public function onTagsResolve(PlayerScoreTagEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (isset($this->timers[$name])) {
            $time = $this->timers[$name];
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;

            $event->setTag(["scorecountdown.timer" => sprintf("%02d:%02d", $minutes, $seconds)]);
        }
    }
}
