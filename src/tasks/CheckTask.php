<?php

declare(strict_types=1);

namespace Donate\tasks;

use Donate\Constant;
use Donate\Donate;
use Donate\StatusCode;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CheckTask extends AsyncTask {

	public function __construct(
		protected mixed $arrayPost,
		protected string $playerName
	) {
	}

	public function onRun(): void {
		$arrayPost = $this->arrayPost;
		if (!is_array($arrayPost)) {
			var_dump(Constant::PREFIX . "Lỗi! Điều gì đó đã khiến cho arrayPost không phải là một mảng?");
			var_dump($arrayPost);
			return;
		}
		$arrayPost["command"] = "check";
		$ch = curl_init(Constant::URL);
		if ($ch === false) {
			var_dump(Constant::PREFIX . "Lỗi: Không thể tạo một phiên cURL mới!");
			return;
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
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (!is_string($raw)) {
			var_dump(Constant::PREFIX . "Lỗi: Thực hiện một phiên cURL đã cho thất bại! (" . curl_error($ch) . ")");
			return;
		}
		$result = json_decode($raw, true);
		$content = [
			"web_status" => $httpCode,
			"result" => $result
		];
		$this->setResult($content);
	}

	public function onCompletion(): void {
		$content = $this->getResult();
		$player = Server::getInstance()->getPlayerExact($this->playerName);
		if (!isset($content) || is_array($content) && $content["result"] == false) {
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "Đã có lỗi xảy ra! Vui lòng thử lại sau.");
			}
			var_dump(Constant::PREFIX . "Lỗi: Không thể lấy thông tin cập nhật. Kết nối hết thời gian chờ?!");
			return;
		}
		if (is_array($content) && $content["web_status"] !== StatusCode::OK) {
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "Đã cố lỗi xảy ra! Vui lòng thử lại sau.");
			}
			var_dump(Constant::PREFIX . "Lỗi: Trạng thái trang web không ổn!");
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::SUCCESS_MATCH_AMOUNT) {
			Donate::getInstance()->successfulDonation(
				playerName: $this->playerName,
				amount: $content["result"]["value"]
			);
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::WAITING_FOR_PROCESSING) {
			Server::getInstance()->getAsyncPool()->submitTask(new CheckTask(
				arrayPost: $content["arrayPost"],
				playerName: $this->playerName
			));
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "Đang kiểm tra thẻ... Vui lòng đợi.");
			}
			return;
		}
		if ($player !== null) {
			if (is_array($content) && $content["result"]["status"] == StatusCode::SUCCESS_NOT_MATCH_AMOUNT) {
				$player->sendMessage(Constant::PREFIX . "Bạn đã chọn sai mệnh giá! Mệnh giá đúng: " . $content["result"]["value"] . ". Mệnh giá bạn đã chọn: " . $content["result"]["declared_value"]);
				return;
			}
			if (is_array($content) && $content["result"]["status"] == StatusCode::FAILED_WITH_REASON) {
				$player->sendMessage(Constant::PREFIX . "Lỗi: " . $content["result"]["message"] . "!");
				return;
			}
			if (!is_array($content)) {
				var_dump(Constant::PREFIX . "Lỗi! Điều gì đó đã khiến cho content không phải là một mảng?");
				var_dump($content);
				return;
			}
			$player->sendMessage(Constant::PREFIX . "Có lỗi xảy ra với mã giao dịch: " . $content["result"]["request_id"] . "! Thông tin lỗi: " .  $content["result"]["message"]);
		}
	}
}
