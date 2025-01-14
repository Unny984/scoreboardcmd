<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

    private ?TaskHandler $countdownTaskHandler = null;

    public function onEnable(): void {
        $this->getLogger()->info(TextFormat::GREEN . "ScoreboardCmd enabled!");
    }

    public function onDisable(): void {
        $this->getLogger()->info(TextFormat::RED . "ScoreboardCmd disabled!");
        if ($this->countdownTaskHandler !== null) {
            $this->countdownTaskHandler->cancel();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return true;
        }

        switch ($command->getName()) {
            case "countdown":
                if (count($args) !== 1 || !is_numeric($args[0])) {
                    $sender->sendMessage(TextFormat::YELLOW . "Usage: /countdown <time_in_seconds>");
                    return true;
                }

                $time = (int)$args[0];
                if ($time <= 0) {
                    $sender->sendMessage(TextFormat::RED . "Please specify a positive number of seconds.");
                    return true;
                }

                $this->startCountdown($sender, $time);
                return true;

            case "stopcountdown":
                $this->stopCountdown($sender);
                return true;
        }

        return false;
    }

    private function startCountdown(Player $player, int $time): void {
        if ($this->countdownTaskHandler !== null) {
            $player->sendMessage(TextFormat::RED . "A countdown is already running.");
            return;
        }

        $player->sendMessage(TextFormat::GREEN . "Countdown started for {$time} seconds.");

        $task = new class($player, $time, $this) extends \pocketmine\scheduler\Task {
            private Player $player;
            private int $remainingTime;
            private Main $plugin;

            public function __construct(Player $player, int $time, Main $plugin) {
                $this->player = $player;
                $this->remainingTime = $time;
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                if ($this->remainingTime <= 0) {
                    $this->plugin->getScheduler()->cancelTask($this);
                    $this->plugin->removeScoreboard($this->player);
                    $this->player->sendMessage(TextFormat::GREEN . "Countdown finished!");
                    return;
                }

                $minutes = intdiv($this->remainingTime, 60);
                $seconds = $this->remainingTime % 60;
                $title = sprintf("%02d:%02d", $minutes, $seconds);
                $this->plugin->updateScoreboard($this->player, $title);

                $this->remainingTime--;
            }
        };

        $this->countdownTaskHandler = $this->getScheduler()->scheduleRepeatingTask($task, 20); // Runs every second
    }

    private function stopCountdown(Player $player): void {
        if ($this->countdownTaskHandler === null) {
            $player->sendMessage(TextFormat::RED . "No countdown is currently running.");
            return;
        }

        $this->countdownTaskHandler->cancel();
        $this->countdownTaskHandler = null;
        $this->removeScoreboard($player);
        $player->sendMessage(TextFormat::GREEN . "Countdown stopped.");
    }

    public function updateScoreboard(Player $player, string $title): void {
        // TODO: Add logic to update the scoreboard title
    }

    public function removeScoreboard(Player $player): void {
        // TODO: Add logic to remove the scoreboard
    }
}
