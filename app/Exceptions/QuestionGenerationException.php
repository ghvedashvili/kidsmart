<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a QuestionTemplate's formula/conditions never resolve after every
 * retry attempt (a broken template — typo, stale variable reference) — signals
 * callers to skip this template and pick another rather than serving a child a
 * question whose "correct answer" is a meaningless fallback.
 */
class QuestionGenerationException extends RuntimeException
{
}
