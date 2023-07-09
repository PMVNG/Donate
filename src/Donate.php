<?php

declare(strict_types=1);

namespace Donate;

use Donate\SingletonTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use pocketmine\utils\Timezone;
use Symfony\Component\Filesystem\Path;

class Donate extends PluginBase {
	use SingletonTrait;

	public MainLogger $logger;

	public Config $donateData;

	protected function onEnable(): void {
		self::setInstance(instance: $this);
		$this->logger = new MainLogger(
			logFile: Path::join($this->getDataFolder(), "log.log"),
			useFormattingCodes: Terminal::hasFormattingCodes(),
			mainThreadName: "Server",
			timezone: new \DateTimeZone(Timezone::get())
		);
		$this->donateData = new Config($this->getDataFolder() . "donateData.yml", Config::YAML);
		if (Constant::ID === "" || Constant::KEY === "") {
			$this->getLogger()->error("Vui lòng không để trống giá trị của Constant::ID và Constant::KEY!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if ($command->getName() == "donate") {
			if (!$sender instanceof Player) {
				$sender->sendMessage(Constant::PREFIX . "Vui lòng sử dụng lệnh này trong trò chơi!");
				return true;
			}
			$sender->sendForm(DonateForm::get());
			return true;
		}
		if ($command->getName() == "topdonate") {
			$donateData = $this->donateData->getAll();
			$maxPage = ceil(count($donateData) / 10);
			if ($maxPage == 0) {
				$sender->sendMessage("Hiện chưa có một ai nạp thẻ ủng hộ máy chủ...");
				return true;
			}
			$max = 0;
			foreach ($donateData as $c) {
				$max += count($donateData);
			}
			$page = ceil($max / 10);
			$page = array_shift($args);
			$page = max(1, $page);
			$page = min($max, $page);
			$page = (int)$page;
			$sender->sendMessage("--- Bảng xếp hạng nạp thẻ trang $page/$maxPage (/topdonate <trang>) ---");
			arsort($donateData);
			$i = 0;
			$senderTop = 0;
			$serverTotalDonated = 0;
			foreach ($donateData as $playerName => $totalDonated) {
				if (($page - 1) * 10 <= $i && $i <= ($page - 1) * 10 + 9) {
					$top = $i + 1;
					if (is_string($playerName)) {
						$playerTotalDonated = $this->donateData->get(k: $playerName);
						if (is_int($playerTotalDonated)) {
							$playerTotalDonated = number_format(num: $playerTotalDonated, decimals: 0, decimal_separator: ".", thousands_separator: ".");
							$sender->sendMessage("$top. $playerName: {$playerTotalDonated}₫");
							$serverTotalDonated = $serverTotalDonated + $totalDonated;
						}
					}
				}
				if ($playerName == $sender->getName()) {
					$senderTop = $i + 1;
				}
				$i++;
			}
			if ($sender instanceof Player) {
				$sender->sendMessage("Xếp hạng của bạn là $senderTop");
			}
			$serverTotalDonated = number_format(num: intval($serverTotalDonated), decimals: 0, decimal_separator: ".", thousands_separator: ".");
			$sender->sendMessage("Tổng số tiền nạp thẻ từ người chơi của máy chủ là: {$serverTotalDonated}₫");
			return true;
		}
		return false;
	}

	public function successfulDonation(string $playerName, string $amount): void {
		$amountFormated = $playerTotalDonated = number_format(num: intval($amount), decimals: 0, decimal_separator: ".", thousands_separator: ".");
		$player = $this->getServer()->getPlayerExact($playerName);
		$this->getServer()->broadcastMessage(Constant::PREFIX . "Người chơi $playerName đã nạp {$amountFormated}₫ để ủng hộ máy chủ!");
		// Mã đưa tiền cho người chơi sẽ được viết tại đây.
		// Ví dụ: EconomyAPI::addMoney($playerName, $amount * Constant::BONUS);
		$playerTotalDonated = $this->donateData->get(k: $playerName, default: 0);
		if (is_int($playerTotalDonated)) {
			$this->donateData->set(k: $playerName, v: $playerTotalDonated + (int) $amount);
			$this->donateData->save();
		}
		if ($player !== null) {
			$player->sendMessage("Chân thành cảm ơn bạn đã ủng hộ máy chủ $amount!");
		}
	}
}
