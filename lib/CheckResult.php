<?php declare(strict_types=1);
namespace Akismet;

/**
 * Specifies the result of a comment check.
 */
enum CheckResult {

	/** The comment is not a spam (i.e. a ham). */
	case ham;

	/** The comment is a spam. */
	case spam;

	/** The comment is a pervasive spam (i.e. it can be safely discarded). */
	case pervasiveSpam;
}
