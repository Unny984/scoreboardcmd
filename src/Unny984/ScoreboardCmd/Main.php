<?php

namespace Unny984\ScoreboardCmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use Ifera\ScoreHud\event\TagsResolveEvent;

class Main extends PluginBase {
    private array $timers = [];

    protected function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this);

        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends \pocketmine\scheduler\Task {
            private Main $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                $this->plugin->updateTimers();
            }
        }, 20); // Runs every second
    }

    public function setTimer(Player $player, int $time): void {
        $this->timers[$player->getName()] = $time;
        $player->sendMessage("Countdown started for {$time} seconds!");
    }

    public function clearTimer(Player $player): void {
        unset($this->timers[$player->getName()]);
        $player->sendMessage("Your countdown has been stopped.");
    }

    public function getTimer(Player $player): ?int {
        return $this->timers[$player->getName()] ?? null;
    }

    public function updateTimers(): void {
        foreach ($this->timers as $name => $time) {
            if ($time > 0) {
                $this->timers[$name]--;
    
                $player = $this->getServer()->getPlayerExact($name);
                if ($player !== null && $player->isOnline()) {
                    // Trigger ScoreHud update with the updated timer
                    $minutes = intdiv($this->timers[$name], 60);
                    $seconds = $this->timers[$name] % 60;
                    $tagValue = sprintf("%02d:%02d", $minutes, $seconds);
    
                    // Use the TagsResolveEvent to dynamically set the tag
                    $event = new \Ifera\ScoreHud\event\TagsResolveEvent($player);
                    $event->setTag(new \Ifera\ScoreHud\scoreboard\ScoreTag("scorecountdown.timer", $tagValue));
                }
            } else {
                unset($this->timers[$name]);
            }
        }
    }    
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        switch ($command->getName()) {
            case "countdown":
                if (isset($args[0]) && is_numeric($args[0])) {
                    $time = (int)$args[0];
                    if ($time > 0) {
                        $this->setTimer($sender, $time);
                        return true;
                    } else {
                        $sender->sendMessage("Please provide a positive number for the countdown.");
                        return false;
                    }
                } else {
                    $sender->sendMessage("Usage: /countdown <time_in_seconds>");
                    return false;
                }

            case "stopcountdown":
                $this->clearTimer($sender);
                return true;

            default:
                return false;
        }
    }
}
