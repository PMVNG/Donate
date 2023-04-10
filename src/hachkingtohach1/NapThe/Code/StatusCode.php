<?php

declare(strict_types = 1);

namespace hachkingtohach1\NapThe\Code;

final class StatusCode {

	/** OK */
	public const OK = 200;

	/** Thẻ thành công đúng mệnh giá */
	public const SUCCESS_MATCH_AMOUNT = 1;

	/** Thẻ thành công sai mệnh giá */
	public const SUCCESS_NOT_MATCH_AMOUNT = 2;

	/** Thẻ lỗi */
	public const ERROR_CARD = 3;

	/** Hệ thống bảo trì */
	public const SYSTEM_MAINTENANCE = 4;

	/** Thẻ chờ xử lý */
	public const WAITING_FOR_PROCESSING = 99;

	/** Gửi thẻ thất bại - Có lý do đi kèm ở phần thông báo trả về */
	public const FAILED_WITH_REASON = 100;
}
