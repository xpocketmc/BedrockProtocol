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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class UpdateBlockSyncedPacket extends UpdateBlockPacket{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_SYNCED_PACKET;

	public const TYPE_NONE = 0;
	public const TYPE_CREATE = 1;
	public const TYPE_DESTROY = 2;

	public int $entityUniqueId;
	public int $updateType;

	protected function decodePayload(PacketSerializer $in) : void{
		parent::decodePayload($in);
		$this->entityUniqueId = $in->getUnsignedVarLong();
		$this->updateType = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		parent::encodePayload($out);
		$out->putUnsignedVarLong($this->entityUniqueId);
		$out->putUnsignedVarLong($this->updateType);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleUpdateBlockSynced($this);
	}
}
