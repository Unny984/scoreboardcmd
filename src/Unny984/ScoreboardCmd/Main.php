<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\ClosureTask;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;

class Main extends PluginBase
{
    private ?TaskHandler $countdownTask = null;
    private int $timeLeft = 0;
    private string $formattedTime = "00:00";

    public function onEnable(): void
    {
        $this->getLogger()->info("ScoreboardCmd enabled!");
        $this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this);
    }    



    public function onDisable(): void
    {
        $this->stopCountdown();
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

            case "debugscorehud":
                $sender->sendMessage("DEBUG: Timer is '{$this->getFormattedTime()}'");
                return true;

            default:
                return false;
        }
        return true;
    }

    private function startCountdown(int $seconds): void
    {
        $this->getLogger()->info("Starting countdown for $seconds seconds...");
        $this->timeLeft = $seconds;
    
        if ($this->countdownTask !== null) {
            $this->countdownTask->cancel();
        }
    
        $this->countdownTask = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->updateCountdown();
        }), 20);
    }
    

    private function stopCountdown(): void
    {
        $this->getLogger()->info("Countdown stopped.");
        if ($this->countdownTask !== null) {
            $this->countdownTask->cancel();
            $this->countdownTask = null;
        }
    
        $this->timeLeft = 0;
        $this->formattedTime = "00:00";
    }
    

    private function updateCountdown(): void
    {
        if ($this->timeLeft <= 0) {
            // Announce when the timer reaches 0
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $this->getServer()->broadcastMessage("§6[Announcement] §aThe countdown has ended!");
            }
    
            // Log the announcement to the console
            $this->getLogger()->info("The countdown has ended!");
    
            // Stop the countdown
            $this->stopCountdown();
            return;
        }
    
        $minutes = intdiv($this->timeLeft, 60);
        $seconds = $this->timeLeft % 60;
    
        $this->formattedTime = sprintf("%02d:%02d", $minutes, $seconds);
    
        // Send action bar message to all players
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $player->sendActionBarMessage("剩余时间: {$this->formattedTime}");
        }
    
        $this->timeLeft--;
    }
    
    

    public function getFormattedTime(): string
    {
        return $this->formattedTime;
    }
}
