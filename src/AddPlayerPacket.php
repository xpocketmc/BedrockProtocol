<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use Ramsey\Uuid\UuidInterface;
use function count;

class AddPlayerPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_PLAYER_PACKET;

	public UuidInterface $uuid;
	public string $username;
	public ?int $entityUniqueId = null; //TODO
	public int $entityRuntimeId;
	public string $platformChatId = "";
	public Vector3 $position;
	public ?Vector3 $motion;
	public float $pitch = 0.0;
	public float $yaw = 0.0;
	public ?float $headYaw = null; //TODO
	public ItemStackWrapper $item;
	/**
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public array $metadata = [];

	//TODO: adventure settings stuff
	public int $uvarint1 = 0;
	public int $uvarint2 = 0;
	public int $uvarint3 = 0;
	public int $uvarint4 = 0;
	public int $uvarint5 = 0;
	public int $long1 = 0;

	/** @var EntityLink[] */
	public array $links = [];
	public string $deviceId = ""; //TODO: fill player's device ID (???)
	public int $buildPlatform = DeviceOS::UNKNOWN;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->uuid = $in->getUUID();
		$this->username = $in->getString();
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->platformChatId = $in->getString();
		$this->position = $in->getVector3();
		$this->motion = $in->getVector3();
		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();
		$this->headYaw = $in->getLFloat();
		$this->item = ItemStackWrapper::read($in);
		$this->metadata = $in->getEntityMetadata();

		$this->uvarint1 = $in->getUnsignedVarInt();
		$this->uvarint2 = $in->getUnsignedVarInt();
		$this->uvarint3 = $in->getUnsignedVarInt();
		$this->uvarint4 = $in->getUnsignedVarInt();
		$this->uvarint5 = $in->getUnsignedVarInt();

		$this->long1 = $in->getLLong();

		$linkCount = $in->getUnsignedVarInt();
		for($i = 0; $i < $linkCount; ++$i){
			$this->links[$i] = $in->getEntityLink();
		}

		$this->deviceId = $in->getString();
		$this->buildPlatform = $in->getLInt();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUUID($this->uuid);
		$out->putString($this->username);
		$out->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putString($this->platformChatId);
		$out->putVector3($this->position);
		$out->putVector3Nullable($this->motion);
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
		$out->putLFloat($this->headYaw ?? $this->yaw);
		$this->item->write($out);
		$out->putEntityMetadata($this->metadata);

		$out->putUnsignedVarInt($this->uvarint1);
		$out->putUnsignedVarInt($this->uvarint2);
		$out->putUnsignedVarInt($this->uvarint3);
		$out->putUnsignedVarInt($this->uvarint4);
		$out->putUnsignedVarInt($this->uvarint5);

		$out->putLLong($this->long1);

		$out->putUnsignedVarInt(count($this->links));
		foreach($this->links as $link){
			$out->putEntityLink($link);
		}

		$out->putString($this->deviceId);
		$out->putLInt($this->buildPlatform);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleAddPlayer($this);
	}
}
