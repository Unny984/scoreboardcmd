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
            $this->getServer()->broadcastMessage("§6[Announcement] §aThe countdown has ended!");
            $this->stopCountdown();
            return;
        }
    
        $minutes = intdiv($this->timeLeft, 60);
        $seconds = $this->timeLeft % 60;
    
        $this->formattedTime = sprintf("%02d:%02d", $minutes, $seconds);
    
        // Update the scoreboard
        $this->updateScoreboard("剩余时间: {$this->formattedTime}");
    
        $this->timeLeft--;
    }
    
    private function updateScoreboard(string $message): void
    {
        // Use ScoreHud to update the scoreboard placeholder
        $pluginManager = $this->getServer()->getPluginManager();
        $scoreHud = $pluginManager->getPlugin("ScoreHud");
    
        // Ensure the plugin is loaded and of the correct type
        if ($scoreHud instanceof \Ifera\ScoreHud\ScoreHud) {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                // Update the player's custom score
                $player->sendMessage("DEBUG: Setting scoreboard message to '$message'"); // Debugging
                // Use ScoreHud's custom placeholder or other APIs here
                // Example placeholder implementation
            }
        } else {
            $this->getLogger()->warning("ScoreHud plugin is not loaded or incompatible.");
        }
    }
    
    
    
    

    public function getFormattedTime(): string
    {
        return $this->formattedTime;
    }
}
