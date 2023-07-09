<?php

declare(strict_types=1);

namespace Donate\tasks;

use Donate\Constant;
use Donate\ErrorCode;
use Donate\StatusCode;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class ChargingTask extends AsyncTask {

	public function __construct(
		protected string $telco,
		protected string $code,
		protected string $serial,
		protected int $amount,
		protected string $playerName
	) {
	}

	public function onRun(): void {
		$dataSign = md5(Constant::KEY . $this->code . $this->serial);
		$arrayPost = [
			"telco" => $this->telco,
			"code" => $this->code,
			"serial" => $this->serial,
			"amount" => $this->amount,
			"request_id" => time(),
			"partner_id" => Constant::ID,
			"sign" => $dataSign,
			"command" => "charging"
		];
		$ch = curl_init(Constant::URL);
		if ($ch === false) {
			var_dump(Constant::PREFIX . "Lỗi: Không thể tạo một phiên cURL mới!" . ErrorCode::AZ001);
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
			var_dump(Constant::PREFIX . "Lỗi: Thực hiện một phiên cURL đã cho thất bại! (" . curl_error($ch) . ")" . ErrorCode::AZ002);
			return;
		}
		$result = json_decode($raw, true);
		$content = [
			"arrayPost" => $arrayPost,
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
				$player->sendMessage(Constant::PREFIX . "Đã có lỗi xảy ra! Vui lòng thử lại sau." . ErrorCode::AZ003);
			}
			var_dump(Constant::PREFIX . "Lỗi: Không thể lấy thông tin cập nhật. Kết nối hết thời gian chờ?!" . ErrorCode::AZ004);
			return;
		}
		if (is_array($content) && $content["web_status"] !== StatusCode::OK) {
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "Đã cố lỗi xảy ra! Vui lòng thử lại sau." . ErrorCode::AZ005);
			}
			var_dump(Constant::PREFIX . "Lỗi: Trạng thái trang web không ổn!" . ErrorCode::AZ006);
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::WAITING_FOR_PROCESSING) {
			$arrayPost = json_encode($content["arrayPost"]);
			if ($arrayPost === false) {
				if ($player !== null) {
					$player->sendMessage(Constant::PREFIX . "Đã có lỗi xảy ra! Vui lòng thử lại sau." . ErrorCode::AZ007);
				}
				var_dump(Constant::PREFIX . 'Lỗi! Chà json_encode($content["arrayPost"]) trả về false' . ErrorCode::AZ008);
				return;
			}
			Server::getInstance()->getAsyncPool()->submitTask(new CheckTask(
				arrayPost: $arrayPost,
				playerName: $this->playerName
			));
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "Đang kiểm tra thẻ... Vui lòng đợi.");
			}
			return;
		}
		if ($player !== null) {
			if (is_array($content) && $content["result"]["status"] == StatusCode::SYSTEM_MAINTENANCE) {
				$player->sendMessage(Constant::PREFIX . "Hệ thống đang bảo trì! Thẻ của bạn vẫn còn giá trị. Vui lòng thử lại sau.");
				return;
			}
			if (is_array($content) && $content["result"]["status"] == StatusCode::FAILED_WITH_REASON) {
				$player->sendMessage(Constant::PREFIX . "Lỗi: " . $content["result"]["message"] . "!" . ErrorCode::AZ009);
				return;
			}
			$player->sendMessage(Constant::PREFIX . "Lỗi không xác định! Vui lòng thử lại sau." . ErrorCode::AZ010);
		}
	}
}
