<?php

declare(strict_types=1);

namespace hachkingtohach1\NapThe\Task;

use hachkingtohach1\NapThe\Main;
use hachkingtohach1\NapThe\Partner;
use hachkingtohach1\NapThe\StatusCode;
use pocketmine\scheduler\AsyncTask;

class CheckTask extends AsyncTask {

	public function __construct(
		protected mixed $arrayPost,
		protected string $playerName
	) {
	}

	public function onRun(): void {
		$arrayPost = $this->arrayPost;
		if (!is_array($arrayPost)) {
			// Waring!
			return;
		}
		$arrayPost["command"] = "check";
		$ch = curl_init(Partner::URL);
		if ($ch === false) {
			throw new \RuntimeException("Unable to create new cURL session");
		}
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_HEADER => false,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => http_build_query($arrayPost)
		]);
		$raw = curl_exec($ch);
		if ($raw === false) {
			throw new \RuntimeException(curl_error($ch));
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		/** @phpstan-ignore-next-line */
		$result = json_decode($raw, true);
		$content = [
			"web_status" => $httpCode,
			"result" => $result
		];
		$this->setResult($content);
	}

	public function onCompletion(): void {
		$main = Main::getInstance();
		$content = $this->getResult();
		$player = $main->getServer()->getPlayerExact($this->playerName);
		if (!isset($content)) {
			$main->getServer()->getLogger()->error("[NapThe] Can't get updated information. Timed out?");
			return;
		}
		if (is_array($content) && $content["web_status"] !== StatusCode::OK) {
			var_dump($content);
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::SUCCESS_MATCH_AMOUNT) {
			/* Nạp Thẻ Thành Công!!! */
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::WAITING_FOR_PROCESSING) {
			$main->getServer()->getAsyncPool()->submitTask(new CheckTask($this->arrayPost, $this->playerName));
			if ($player == null) {
				return;
			}
			$player->sendTip("§l✾§aĐang kiểm tra thẻ, xin §cđừng chat§a lúc này vì bạn sẽ không nhận được bảng thông tin phản hồi...");
			return;
		}

		if ($player !== null) { /* Kiểm tra xem người chơi có đang trực tuyến hay không? Nếu có thì gửi các thông báo liên quan. */
			if (is_array($content) && $content["result"]["status"] == StatusCode::SUCCESS_NOT_MATCH_AMOUNT) {
				$player->sendMessage("[Donate] Nạp thẻ thành công! (Sai mệnh giá)");
			} else {
				$player->sendMessage("[Donate] Lỗi không xác định. Vui lòng kiểm tra lại các thông tin của thẻ hoặc thông báo cho quản trị viên.");
			}
		}
	}
}
