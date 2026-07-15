<?php
declare(strict_types=1); namespace Sif\Foundation\Exceptions; use Sif\Foundation\RuntimeState; final class InvalidRuntimeTransitionException extends RuntimeException { public static function between(RuntimeState $from,RuntimeState $to):self{return new self(sprintf('Invalid runtime transition from %s to %s.',$from->value,$to->value));} }
