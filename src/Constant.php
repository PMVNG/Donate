<?php

declare(strict_types=1);

namespace Donate;

class Constant {

	public const PREFIX = "[Nạp Thẻ] ";

	public const ID = "";

	public const KEY = "";

	public const URL = "https://trumthe.vn//chargingws/v2";

	/** Số tiền người chơi nhận được trong máy chủ khi nạp thẻ thành công sẽ nhân với giá trị này */
	public const BONUS = 1;

	/** Không thay đổi giá trị của hằng này */
	public const TELCO = [
		"VIETTEL",
		"VINA",
		"MOBI",
		"VIETNAMMOBI",
		"ZING",
		"GARENA",
		"VCOIN",
		"GATE"
	];

	public const TELCO_DISPLAY = [
		"Viettel",
		"VinaPhone",
		"MobiFone",
		"Vietnamobile",
		"Zing",
		"Garena",
		"VCoin",
		"Gate"
	];

	/** Không thay đổi giá trị của hằng này */
	public const AMOUNT =  [
		10000,
		20000,
		50000,
		100000,
		200000,
		500000
	];

	public const AMOUNT_DISPLAY =  [
		"10.000₫",
		"20.000₫",
		"50.000₫",
		"100.000₫",
		"200.000₫",
		"500.000₫",
	];
}
