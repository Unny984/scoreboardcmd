<?php
namespace ScoreboardCountdown;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\entity\Living;
use pocketmine\entity\Human;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
    private $countdowns = [];
    private $tasks = [];

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TF::GREEN . "ScoreboardCountdown plugin enabled!");
    }

    public function onDisable() {
        $this->getLogger()->info(TF::RED . "ScoreboardCountdown plugin disabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game.");
            return false;
        }

        switch ($command->getName()) {
            case "countdown":
                if (count($args) < 1) {
                    $sender->sendMessage(TF::RED . "Usage: /countdown <time_in_seconds>");
                    return false;
                }

                $time = (int)$args[0];
                if ($time <= 0) {
                    $sender->sendMessage(TF::RED . "Time must be greater than zero.");
                    return false;
                }

                $this->startCountdown($sender, $time);
                return true;

            case "stopcountdown":
                $this->stopCountdown($sender);
                return true;

            default:
                return false;
        }
    }

    private function startCountdown(Player $player, int $time) {
        if (isset($this->tasks[$player->getName()])) {
            $player->sendMessage(TF::RED . "A countdown is already running.");
            return;
        }

        $this->tasks[$player->getName()] = $this->getScheduler()->scheduleRepeatingTask(new class($this, $player, $time) extends Task {
            private $plugin;
            private $player;
            private $time;

            public function __construct(Main $plugin, Player $player, int $time) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->time = $time;
            }

            public function onRun(int $currentTick) {
                if ($this->time <= 0) {
                    $this->plugin->stopCountdown($this->player);
                    $this->player->sendMessage(TF::GREEN . "Countdown finished!");
                    return;
                }

                $minutes = intdiv($this->time, 60);
                $seconds = $this->time % 60;
                $title = sprintf("%02d:%02d", $minutes, $seconds);

                $this->plugin->updateScoreboard($this->player, $title);

                $this->time--;
            }
        }, 20);

        $player->sendMessage(TF::GREEN . "Countdown started for $time seconds.");
    }

    private function stopCountdown(Player $player) {
        if (!isset($this->tasks[$player->getName()])) {
            $player->sendMessage(TF::RED . "No countdown is running.");
            return;
        }

        $this->tasks[$player->getName()]->cancel();
        unset($this->tasks[$player->getName()]);

        $this->removeScoreboard($player);

        $player->sendMessage(TF::YELLOW . "Countdown stopped.");
    }

    private function updateScoreboard(Player $player, string $title) {
        // Your scoreboard handling logic here.
        // This is pseudo-code as scoreboard implementation varies.
        // Use an appropriate library or API to handle it.

        $player->sendPopup($title); // Temporary example, replace with actual scoreboard logic.
    }

    private function removeScoreboard(Player $player) {
        // Logic to remove the scoreboard from the player.
        // Replace with actual implementation based on the library you're using.
        $player->sendPopup(""); // Temporary example.
    }
}
