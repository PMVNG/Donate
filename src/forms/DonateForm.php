<?php

declare(strict_types=1);

namespace Donate\forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\StepSlider;
use Donate\Constant;
use pocketmine\player\Player;

class DonateForm {

	public static function get(): CustomForm {
		return new CustomForm(
			title: "Biểu Mẫu Nạp Thẻ",
			elements: [
				new Dropdown(name: "telco", text: "Loại thẻ", options: Constant::TELCO),
				new StepSlider(name: "amount", text: "Mệnh giá", options: Constant::AMOUNT),
				new Input(name: "serial", text: "Số sê-ri", hintText: "Nhập số sê-ri tại đây:\nVí dụ: 10004783347874"),
				new Input(name: "code", text: "Mã thẻ", hintText: "Nhập mã thẻ tại đây:\nVí dụ: 312821445892982")
			],
			onSubmit: function(Player $submitter, CustomFormResponse $response) : void{
				var_dump($response);
			},
		);
	}
}
