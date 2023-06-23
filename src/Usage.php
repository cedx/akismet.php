<?php namespace akismet;

/**
 * Provides API usage for a given month.
 */
class Usage {

	/**
	 * The number of monthly API calls your plan entitles you to.
	 */
	readonly int $limit;

	/**
	 * The percentage of the limit used since the beginning of the month.
	 */
	readonly float $percentage;

	/**
	 * Value indicating whether the requests are being throttled for having consistently gone over the limit.
	 */
	readonly bool $throttled;

	/**
	 * The number of calls (spam + ham) since the beginning of the month.
	 */
	readonly int $usage;

	/**
	 * Creates a new usage.
	 * @param int $usage The number of calls (spam + ham) since the beginning of the month.
	 * @param int $limit The number of monthly API calls your plan entitles you to.
	 * @param float $percentage The percentage of the limit used since the beginning of the month.
	 * @param bool $throttled Value indicating whether the requests are being throttled for having consistently gone over the limit.
	 */
	function __construct(int $usage, int $limit, float $percentage, bool $throttled) {
		$this->limit = $limit;
		$this->percentage = $percentage;
		$this->throttled = $throttled;
		$this->usage = $usage;
	}

	/**
	 * Creates a new usage from the specified JSON object.
	 * @param object $json A JSON object representing a usage.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $json): self {
		return new self(
			limit: isset($json->limit) && is_int($json->limit) ? $json->limit : -1,
			percentage: isset($json->percentage) && is_numeric($json->percentage) ? $json->percentage : 0,
			throttled: isset($json->throttled) && is_bool($json->throttled) ? $json->throttled : false,
			usage: isset($json->usage) && is_int($json->usage) ? $json->usage : 0
		);
	}
}
