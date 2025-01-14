<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;

class Main extends PluginBase {

    private ?TimerAddon $timerAddon = null;

    public function onEnable(): void {
        $this->timerAddon = new TimerAddon($this);
        $this->getLogger()->info("ScoreboardCmd has been enabled!");
    }

    public function startCountdown(Player $player, int $time): void {
        if ($this->timerAddon !== null) {
            $this->timerAddon->setTimer($player, $time);
        }
    }

    public function stopCountdown(Player $player): void {
        if ($this->timerAddon !== null) {
            $this->timerAddon->clearTimer($player);
        }
    }
}
