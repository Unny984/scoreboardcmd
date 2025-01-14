<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    private ?TimerAddon $timerAddon = null;

    public function onEnable(): void {
        $this->timerAddon = new TimerAddon($this);
        $this->getLogger()->info("ScoreboardCmd has been enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        switch ($command->getName()) {
            case "countdown":
                // Ensure there is exactly one argument and it is numeric
                if (count($args) !== 1 || !ctype_digit($args[0])) {
                    $sender->sendMessage("Usage: /countdown <time_in_seconds>");
                    return false;
                }

                $time = (int)$args[0];
                if ($time <= 0) {
                    $sender->sendMessage("Please specify a positive number of seconds.");
                    return false;
                }

                $this->startCountdown($sender, $time);
                $sender->sendMessage("Countdown started for {$time} seconds.");
                return true;

            case "stopcountdown":
                $this->stopCountdown($sender);
                $sender->sendMessage("Countdown stopped.");
                return true;
        }

        return false;
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
