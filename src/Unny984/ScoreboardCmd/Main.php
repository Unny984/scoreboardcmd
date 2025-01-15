<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;

class Main extends PluginBase implements Listener {
    protected int $countdownTime = 0;
    protected ?TaskHandler $countdownTask = null;
    protected bool $isCountdownActive = false;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function getCountdownTime(): int {
        return $this->countdownTime;
    }

    public function setCountdownTime(int $time): void {
        $this->countdownTime = $time;
    }

    public function isCountdownActive(): bool {
        return $this->isCountdownActive;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch($command->getName()) {
            case "countdown":
                if (!isset($args[0]) || !is_numeric($args[0])) {
                    $sender->sendMessage("§cUsage: /countdown <time_in_seconds>");
                    return false;
                }

                $time = (int)$args[0];
                if ($time <= 0) {
                    $sender->sendMessage("§cTime must be greater than 0!");
                    return false;
                }

                $this->startCountdown($time);
                $sender->sendMessage("§aCountdown started for {$time} seconds!");
                return true;

            case "stopcountdown":
                if (!$this->isCountdownActive) {
                    $sender->sendMessage("§cNo countdown is currently active!");
                    return false;
                }

                $this->stopCountdown();
                $sender->sendMessage("§aCountdown stopped!");
                return true;
        }
        return false;
    }

    private function startCountdown(int $seconds): void {
        $this->stopCountdown(); // Stop any existing countdown
        $this->countdownTime = $seconds;
        $this->isCountdownActive = true;

        $this->countdownTask = $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                $time = $this->plugin->getCountdownTime();
                if ($time <= 0) {
                    $this->plugin->stopCountdown();
                    return;
                }

                $this->plugin->setCountdownTime($time - 1);
                $this->plugin->updateScoreHudTags();
            }
        }, 20); // Run every second (20 ticks)
    }

    private function stopCountdown(): void {
        if ($this->countdownTask instanceof TaskHandler) {
            $this->countdownTask->cancel();
            $this->countdownTask = null;
        }
        $this->countdownTime = 0;
        $this->isCountdownActive = false;
        $this->updateScoreHudTags();
    }

    private function updateScoreHudTags(): void {
        $minutes = floor($this->countdownTime / 60);
        $seconds = $this->countdownTime % 60;
        $value = $this->isCountdownActive ? sprintf("%02d:%02d", $minutes, $seconds) : "No timer";
        
        $tag = new ScoreTag("scorecountdown.timer", $value);
        
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $ev = new PlayerTagUpdateEvent($player, $tag);
            $ev->call();
        }
    }

    /**
     * @param TagsResolveEvent $event
     * @priority NORMAL
     */
    public function onTagResolve(TagsResolveEvent $event): void {
        $tag = $event->getTag();
        
        if ($tag->getName() !== "scorecountdown.timer") {
            return;
        }

        $minutes = floor($this->countdownTime / 60);
        $seconds = $this->countdownTime % 60;
        $tag->setValue($this->isCountdownActive ? sprintf("%02d:%02d", $minutes, $seconds) : "No timer");
    }
}