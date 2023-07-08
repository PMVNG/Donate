<?php

declare(strict_types=1);

namespace Donate\tasks;

use Donate\Constant;
use Donate\Donate;
use Donate\StatusCode;
use pocketmine\scheduler\AsyncTask;

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
			"arrayPost" => $arrayPost,
			"web_status" => $httpCode,
			"result" => $result
		];
		$this->setResult($content);
	}

	public function onCompletion(): void {
		$main = Donate::getInstance();
		$content = $this->getResult();
		$player = $main->getServer()->getPlayerExact($this->playerName);
		if (!isset($content)) {
			$main->getServer()->getLogger()->error(Constant::PREFIX . "Can't get updated information. Timed out?");
			return;
		}
		if (is_array($content) && $content["result"] == false) {
			if ($player !== null) {
				$player->sendMessage(Constant::PREFIX . "API đối tác có lỗi không xác đinh. Vui lòng thử lại sau.");
			}
		}
		if (is_array($content) && $content["web_status"] !== StatusCode::OK) {
			var_dump($content);
			return;
		}
		if (is_array($content) && $content["result"]["status"] == StatusCode::WAITING_FOR_PROCESSING) {
			$main->getServer()->getAsyncPool()->submitTask(new CheckTask(
				arrayPost: $content["arrayPost"],
				playerName: $this->playerName
			));
			if ($player !== null) {
				$player->sendTip(Constant::PREFIX . "Đang kiểm tra thẻ...");
			}
			return;
		}
		if ($player !== null) { /* Kiểm tra xem người chơi có đang trực tuyến hay không? Nếu có thì gửi các thông báo liên quan. */
			if (is_array($content) && $content["result"]["status"] == StatusCode::SYSTEM_MAINTENANCE) {
				$player->sendMessage($this->telco . " đang bảo trì. Thẻ của bạn vẫn còn giá trị. Vui lòng thử lại sau.");
				return;
			}
			if (is_array($content) && $content["result"]["status"] == StatusCode::FAILED_WITH_REASON) {
				$player->sendMessage("[Donate] Lỗi: " . $content["result"]["message"]);
			} else {
				$player->sendMessage("[Donate] Lỗi không xác định. Vui lòng thông báo cho quản trị viên.");
			}
		}
	}
}
