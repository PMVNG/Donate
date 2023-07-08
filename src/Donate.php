<?php

declare(strict_types=1);

namespace Donate;

use Donate\forms\DonateForm;
use Donate\SingletonTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Donate extends PluginBase {
	use SingletonTrait;

	public static self $instance;

	protected function onEnable(): void {
		self::setInstance($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Constant::PREFIX . "Bạn không thể sử dụng lệnh này trong thiết bị đầu cuối!");
			return true;
		}
		if ($command->getName() == "donate") {
			$sender->sendForm(DonateForm::get());
			return true;
		}
		return false;
	}
}
