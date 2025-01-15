<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use ScoreHud\ScoreHud;

class Main extends PluginBase
{
    private ?ClosureTask $countdownTask = null;
    private int $timeLeft = 0;

    public function onEnable(): void
    {
        $this->getLogger()->info("ScoreboardCmd enabled!");
    }

    public function onDisable(): void
    {
        if ($this->countdownTask !== null) {
            $this->getScheduler()->cancelTask($this->countdownTask->getHandler()->getId());
        }
        $this->getLogger()->info("ScoreboardCmd disabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        switch ($command->getName()) {
            case "countdown":
                if (count($args) !== 1 || !is_numeric($args[0])) {
                    $sender->sendMessage("Usage: /countdown <time_in_seconds>");
                    return false;
                }

                $this->startCountdown((int)$args[0]);
                $sender->sendMessage("Countdown started for {$args[0]} seconds!");
                break;

            case "stopcountdown":
                $this->stopCountdown();
                $sender->sendMessage("Countdown stopped and scoreboard cleared.");
                break;

            default:
                return false;
        }

        return true;
    }

    private function startCountdown(int $seconds): void
    {
        $this->timeLeft = $seconds;

        if ($this->countdownTask !== null) {
            $this->getScheduler()->cancelTask($this->countdownTask->getHandler()->getId());
        }

        $this->countdownTask = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->updateCountdown();
        }), 20); // Schedule task to run every second
    }

    private function stopCountdown(): void
    {
        if ($this->countdownTask !== null) {
            $this->getScheduler()->cancelTask($this->countdownTask->getHandler()->getId());
            $this->countdownTask = null;
        }

        $this->clearScoreboard();
    }

    private function updateCountdown(): void
    {
        if ($this->timeLeft <= 0) {
            $this->stopCountdown();
            return;
        }

        $minutes = intdiv($this->timeLeft, 60);
        $seconds = $this->timeLeft % 60;

        $this->updateScoreboardTitle(sprintf("%02d:%02d", $minutes, $seconds));
        $this->timeLeft--;
    }

    private function updateScoreboardTitle(string $title): void
    {
        $scoreHud = $this->getServer()->getPluginManager()->getPlugin("ScoreHud");

        if ($scoreHud instanceof ScoreHud) {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $scoreHud->setCustomScore($player, $title);
            }
        }
    }

    private function clearScoreboard(): void
    {
        $scoreHud = $this->getServer()->getPluginManager()->getPlugin("ScoreHud");

        if ($scoreHud instanceof ScoreHud) {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $scoreHud->resetCustomScore($player);
            }
        }
    }
}
