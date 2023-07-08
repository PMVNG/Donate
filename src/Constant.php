<?php

declare(strict_types=1);

namespace Donate;

final class Constant {

	public const PREFIX = "[Nạp Thẻ] ";

	public const ID = "";

	public const KEY = "";

	public const URL = "https://trumthe.vn//chargingws/v2";

	public const TELCO = [
		Telco::VIETTEL,
		Telco::VINA,
		Telco::MOBI,
		Telco::VIETNAMMOBI,
		Telco::ZING,
		Telco::GARENA,
		Telco::VCOIN,
		Telco::GATE
	];

	public const AMOUNT =  [
		"10000",
		"20000",
		"50000",
		"100000",
		"200000",
		"500000"
	];
}
