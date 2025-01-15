<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    private TimerAddon $timerAddon;

    public function onEnable(): void {
        $this->timerAddon = new TimerAddon($this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        switch ($command->getName()) {
            case "countdown":
                if (isset($args[0]) && is_numeric($args[0])) {
                    $time = (int)$args[0];
                    $this->timerAddon->setTimer($sender, $time);
                    $sender->sendMessage("Countdown started for {$time} seconds.");
                } else {
                    $sender->sendMessage("Usage: /countdown <time_in_seconds>");
                }
                return true;

            case "stopcountdown":
                $this->timerAddon->clearTimer($sender);
                $sender->sendMessage("Countdown stopped.");
                return true;

            case "debugscorehud":
                $time = $this->timerAddon->getTimer($sender);
                $timeMessage = $time !== null ? "{$time} seconds remaining." : "No active timer.";
                $sender->sendMessage("Debug info: $timeMessage");
                return true;
        }

        return false;
    }
}
