<?php namespace akismet;

/**
 * Provides API usage for a given month.
 */
final readonly class Usage {

	/**
	 * The number of monthly API calls your plan entitles you to.
	 */
	public int $limit;

	/**
	 * The percentage of the limit used since the beginning of the month.
	 */
	public float $percentage;

	/**
	 * Value indicating whether the requests are being throttled for having consistently gone over the limit.
	 */
	public bool $throttled;

	/**
	 * The number of calls (spam + ham) since the beginning of the month.
	 */
	public int $usage;

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
			limit: (int) ($json->limit ?? -1),
			percentage: (float) ($json->percentage ?? 0),
			throttled: (bool) ($json->throttled ?? false),
			usage: (int) ($json->usage ?? 0)
		);
	}
}
