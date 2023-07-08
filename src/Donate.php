<?php

declare(strict_types=1);

namespace Donate;

use Donate\forms\DonateForm;
use Donate\SingletonTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use pocketmine\utils\Timezone;
use Symfony\Component\Filesystem\Path;

class Donate extends PluginBase {
	use SingletonTrait;

	public MainLogger $logger;

	protected function onEnable(): void {
		self::setInstance($this);
		$this->logger = new MainLogger(Path::join($this->getDataFolder(), "log.log"), Terminal::hasFormattingCodes(), "Server", new \DateTimeZone(Timezone::get()));
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Constant::PREFIX . "Vui lòng sử dụng lệnh này trong trò chơi!");
			return true;
		}
		if ($command->getName() == "donate") {
			$sender->sendForm(DonateForm::get());
			return true;
		}
		return false;
	}
}
