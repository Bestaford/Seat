<?php

namespace Bestaford;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\block\Stair;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

class Seat extends PluginBase implements Listener {
	
	public $chairs = [];

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if($player->isOnGround() && $player->isFlying() === false) {
			if($block instanceof Stair && $block->getDamage() < 4) {
				if(!$this->isChairAt($block)) {
					$this->seatOn($player, $block);
				}
			}
		}
	}

	public function onPacketReceived(DataPacketReceiveEvent $event) {
		$pk = $event->getPacket();
		if($pk::NETWORK_ID == 0x24) {
			if($pk->action == 8) {
				$this->removeChair($event->getPlayer(), true);
			}
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$this->removeChair($event->getPlayer());
	}

	public function onEntityTeleport(EntityTeleportEvent $event) {
		$this->removeChair($event->getEntity());
	}

	public function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$this->removeChair($event->getEntity());
	}

	public function onEntityDeath(EntityDeathEvent $event) {
		$this->removeChair($event->getEntity());
	}

	public function seatOn($player, $block) {
		$location = $player->getLocation();
		if($this->onChair($player)) {
			$location = $this->getChair($player)["location"];
		}
		$this->removeChair($player);
		$chair = new Chair($player, $block);
		$this->chairs[$player->getId()] = ["chair" => $chair, "location" => $location];
		$chair->spawnToAll();
		$player->sendTip(" ");
	}

	public function removeChair($player, $exit = false) {
		if($this->onChair($player)) {
			$chair = $this->getChair($player);
			if($exit) {
				$player->teleport($chair["location"]->asVector3(), $player->yaw, $player->pitch);
			} else {
				$chair["chair"]->despawnFromAll();
			}
			unset($this->chairs[$player->getId()]);
		}
	}

	public function onChair($player) {
		return isset($this->chairs[$player->getId()]);
	}

	public function getChair($player) {
		if($this->onChair($player)) {
			return $this->chairs[$player->getId()];
		} else {
			return false;
		}
	}

	public function isChairAt($block) {
		foreach($this->chairs as $key => $value) {
			$chair = $value["chair"];
			if($chair->x == $block->x && $chair->y == $block->y && $chair->z == $block->z && $chair->level == $block->getLevel())
 				return true;
		}
		return false;
	}

}