<?php

declare(strict_types=1);

namespace Donate;

class Constant {

	public const PREFIX = "[Nạp Thẻ] ";

	public const ID = "";

	public const KEY = "";

	public const URL = "https://trumthe.vn//chargingws/v2";

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

	public const AMOUNT =  [
		"10000",
		"20000",
		"50000",
		"100000",
		"200000",
		"500000"
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
