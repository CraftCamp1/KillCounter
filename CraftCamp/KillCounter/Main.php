<?php

namespace CraftCamp\KillCounter;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener {

    private $kills = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
   public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
    if ($command->getName() === "kills") {
        if (isset($args[0])) {
            $playerName = strtolower($args[0]);
            $players = $this->getServer()->getOnlinePlayers();
            $exactMatchPlayers = [];
            $partialMatchPlayers = [];
            foreach ($players as $player) {
                if (strtolower($player->getName()) === $playerName) {
                    $exactMatchPlayers[] = $player;
                } elseif (strpos(strtolower($player->getName()), $playerName) === 0) {
                    $partialMatchPlayers[] = $player;
                }
            }
            if (count($exactMatchPlayers) > 0) {
                $matchingPlayers = $exactMatchPlayers;
            } elseif (count($partialMatchPlayers) > 0) {
                $matchingPlayers = $partialMatchPlayers;
            } else {
                $matchingPlayers = [];
            }
            if (count($matchingPlayers) === 0) {
                $sender->sendMessage("Player not found.");
            } elseif (count($matchingPlayers) > 1) {
                $sender->sendMessage("Please be more specific.");
            } else {
                $player = $matchingPlayers[0];
                $displayName = ucwords($player->getDisplayName());
                $kills = $this->getKills($player->getName());
                $sender->sendMessage("$displayName has $kills kills.");
            }
        } else {
            $player = $sender;
            $displayName = ucwords($player->getDisplayName());
            $kills = $this->getKills($player->getName());
            $player->sendMessage("You have $kills kills.");
        }
        return true;
    }
    return false;
}


    
    public function onPlayerDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        $damage = $player->getLastDamageCause();
        if($damage instanceof EntityDamageByEntityEvent){
            $killer = $damage->getDamager();
        if ($killer instanceof Player) {
            $this->setKills($killer->getName(), $this->getKills($killer->getName()) + 1);
            }
        }
    }



    private function getKills(string $playerName): int {
        if (isset($this->kills[$playerName])) {
            return $this->kills[$playerName];
        }
        return 0;
    }

    private function setKills(string $playerName, int $kills) {
        $this->kills[$playerName] = $kills;
    }
    
}
