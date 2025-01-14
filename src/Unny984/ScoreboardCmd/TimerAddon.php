<?php

namespace Unny984\ScoreCountdown;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\ScoreHud;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class TimerAddon implements Listener {

    private PluginBase $plugin;
    protected array $timers = []; // Changed to protected

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function startTimer(Player $player, int $time): void {
        $this->timers[$player->getName()] = $time;
        $this->plugin->getScheduler()->scheduleRepeatingTask(new class($this, $player) extends Task {
            private TimerAddon $addon;
            private Player $player;

            public function __construct(TimerAddon $addon, Player $player) {
                $this->addon = $addon;
                $this->player = $player;
            }

            public function onRun(): void {
                $name = $this->player->getName();
                if (!isset($this->addon->timers[$name])) {
                    return;
                }

                $time = $this->addon->timers[$name]--;
                if ($time <= 0) {
                    $this->addon->stopTimer($this->player);
                }
            }
        }, 20);
    }

    public function stopTimer(Player $player): void {
        unset($this->timers[$player->getName()]);
    }

    /**
     * Handle ScoreHud placeholders
     */
    public function onTagsResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (isset($this->timers[$name])) {
            $time = $this->timers[$name];
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;
            $event->setTag("scorecountdown.timer", sprintf("%02d:%02d", $minutes, $seconds));
        }
    }
}
