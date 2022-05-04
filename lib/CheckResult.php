<?php declare(strict_types=1);
namespace Akismet;

/** Specifies the result of a comment check. */
abstract class CheckResult {

	/** The comment is not a spam (i.e. a ham). */
	const isHam = "isHam";

	/** The comment is a pervasive spam (i.e. it can be safely discarded). */
	const isPervasiveSpam = "isPervasiveSpam";

	/** The comment is a spam. */
	const isSpam = "isSpam";
}
