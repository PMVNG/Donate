<?php

declare(strict_types=1);

namespace hachkingtohach1\NapThe;

use jojoe77777\FormAPI\CustomForm;
use hachkingtohach1\NapThe\Partner\Partner;
use hachkingtohach1\NapThe\Task\ChargingTask;
use hachkingtohach1\NapThe\Utils\SingletonTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	use SingletonTrait;

	public static self $instance;

	protected function onEnable(): void {
		self::setInstance($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cBạn không thể sử dụng lệnh này trong thiết bị đầu cuối!");
			return true;
		}
		if ($command->getName() == "napthe") {
			$form = new CustomForm(function (Player $sender, $data) {
				// TODO: Check $data
				$this->getServer()->getAsyncPool()->submitTask(new ChargingTask(
					telco: Partner::TELCO[$data[0]],
					code: $data[3],
					serial: $data[2],
					amount: (int) Partner::AMOUNT[$data[1]],
					playerName: $sender->getName()
				));
			});
			$form->setTitle(title: "Biểu Mẫu Nạp Thẻ");
			$form->addDropdown(text: "Loại thẻ:", options: Partner::TELCO);
			$form->addStepSlider(text: "Mệnh giá", steps: Partner::AMOUNT);
			$form->addInput(text: "Số sê-ri:", placeholder: "10004783347874");
			$form->addInput(text: "Mã thẻ:", placeholder: "312821445892982");
			$sender->sendForm($form);
			return true;
		}
		return false;
	}
}
