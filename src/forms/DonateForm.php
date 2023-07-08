<?php

declare(strict_types=1);

namespace Donate\forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use Donate\Constant;
use pocketmine\player\Player;

class DonateForm {

	public static function get(): CustomForm {
		return new CustomForm(
			title: "Biểu Mẫu Nạp Thẻ",
			elements: [
				new Dropdown("telco", text: "Loại thẻ", options: Constant::TELCO)
			],
			onSubmit: function(Player $submitter, CustomFormResponse $response) : void{
				var_dump($response);
			},
		);
	}
}
