<?php

namespace Bestaford;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;

class Chair extends Position {
	
	private $id;
	private $player;

	public function __construct(Player $player, Block $block) {
		parent::__construct($block->x, $block->y, $block->z, $block->getLevel());
		$this->id = Entity::$entityCount++;
		$this->player = $player;
	}

	public function spawnTo(Player $player) {
		$pk = new AddEntityPacket();
		$pk->eid = $this->id;
		$pk->type = 84;
		$pk->x = $this->x + 0.5;
		$pk->y = $this->y + 1.2;
		$pk->z = $this->z + 0.5;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = $this->player->yaw;
		$pk->pitch = $this->player->pitch;
		$flags = 1 << Entity::DATA_FLAG_INVISIBLE;
		$flags ^= 1 << Entity::DATA_FLAG_NO_AI;
		$flags ^= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
		$flags ^= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
		$pk->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags]];
		$player->dataPacket($pk);
		$pk = new SetEntityLinkPacket();
		$pk->from = $this->id;
		$pk->to = $this->player->getId();
		$pk->type = 1;
		$player->dataPacket($pk);
	}

	public function spawnToAll() {
		foreach($this->level->getPlayers() as $player) {
			$this->spawnTo($player);
		}
	}

	public function despawnFrom(Player $player) {
		$pk = new RemoveEntityPacket();
		$pk->eid = $this->id;
		$player->dataPacket($pk);
	}

	public function despawnFromAll() {
		foreach($this->level->getPlayers() as $player) {
			$this->despawnFrom($player);
		}
	}

}