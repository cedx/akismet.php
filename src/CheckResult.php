<?php declare(strict_types=1);
namespace Akismet;

/** Specifies the result of a comment check. */
abstract class CheckResult {

	/** @var string The comment is not a spam (i.e. a ham). */
	const isHam = "isHam";

	/** @var string The comment is a pervasive spam (i.e. it can be safely discarded). */
	const isPervasiveSpam = "isPervasiveSpam";

	/** @var string The comment is a spam. */
	const isSpam = "isSpam";
}
