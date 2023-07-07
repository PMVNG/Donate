<?php

declare(strict_types=1);

namespace hachkingtohach1\NapThe;

trait SingletonTrait {

	public static Main $instance;

	public static function setInstance(Main $instance): void {
		self::$instance = $instance;
	}

	public static function getInstance(): Main {
		return self::$instance;
	}
}
