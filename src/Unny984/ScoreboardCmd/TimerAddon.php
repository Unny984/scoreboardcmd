<?php

namespace Unny984\ScoreboardCmd;

use Ifera\ScoreHud\addon\Addon;
use pocketmine\player\Player;

class TimerAddon extends Addon {

    private array $timers = [];

    public function __construct($plugin) {
        parent::__construct($plugin);
    }

    public function getProcessedTags(Player $player): array {
        $name = $player->getName();
        if (isset($this->timers[$name])) {
            $time = $this->timers[$name];
            $minutes = intdiv($time, 60);
            $seconds = $time % 60;
            return [
                "scorecountdown.timer" => sprintf("%02d:%02d", $minutes, $seconds)
            ];
        }

        return [
            "scorecountdown.timer" => "00:00"
        ];
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
                $this->timers[$name]--;
            } else {
                unset($this->timers[$name]);
            }
        }
    }
}
