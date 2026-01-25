<?php declare(strict_types=1);
namespace Belin\Akismet;

/**
 * Provides API usage for a given month.
 */
final class Usage {

	/**
	 * The number of monthly API calls your plan entitles you to.
	 */
	public readonly int $limit;

	/**
	 * The percentage of the limit used since the beginning of the month.
	 */
	public readonly float $percentage;

	/**
	 * Value indicating whether the requests are being throttled for having consistently gone over the limit.
	 */
	public readonly bool $throttled;

	/**
	 * The number of calls (spam + ham) since the beginning of the month.
	 */
	public readonly int $usage;

	/**
	 * Creates a new usage.
	 * @param int $usage The number of calls (spam + ham) since the beginning of the month.
	 * @param int $limit The number of monthly API calls your plan entitles you to.
	 * @param float $percentage The percentage of the limit used since the beginning of the month.
	 * @param bool $throttled Value indicating whether the requests are being throttled for having consistently gone over the limit.
	 */
	public function __construct(int $usage, int $limit, float $percentage, bool $throttled) {
		$this->limit = $limit;
		$this->percentage = $percentage;
		$this->throttled = $throttled;
		$this->usage = $usage;
	}
}
