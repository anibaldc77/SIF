<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

use Sif\Foundation\Contracts\ActionServiceResolverInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestBodyParserInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Controller\Input\RequestInput;
use Sif\Foundation\Controller\Input\RequestInputValue;
use Throwable;

final readonly class ActionArgumentResolver
{
    public function __construct(
        private ?RequestBodyParserInterface $bodyParser = null,
        private ?ActionServiceResolverInterface $serviceResolver = null,
    ) {
    }

    /** @param list<ActionArgumentDefinition> $definitions */
    public function resolve(
        array $definitions,
        RequestInterface $request,
        ?ExecutionContextInterface $context = null,
    ): ActionArgumentResolution {
        $issues = [];
        $body = [];

        if ($this->usesBody($definitions) && !$request->body()->isEmpty()) {
            if ($this->bodyParser === null || !$this->bodyParser->supports($request->body())) {
                $issues[] = new ActionArgumentIssue(
                    'body.unsupported_media_type',
                    '*',
                    ActionArgumentSource::Body,
                    'No request-body parser supports the supplied media type.',
                );
            } else {
                try {
                    $body = $this->bodyParser->parse($request->body());
                } catch (Throwable) {
                    $issues[] = new ActionArgumentIssue(
                        'body.parse_failed',
                        '*',
                        ActionArgumentSource::Body,
                        'The request body could not be parsed safely.',
                    );
                }
            }
        }

        $input = RequestInput::fromRequest($request, $body);
        $arguments = [];
        $named = [];

        foreach ($definitions as $definition) {
            [$value, $issue] = $this->resolveDefinition($definition, $input, $request, $context);
            if ($issue !== null) {
                $issues[] = $issue;
                continue;
            }
            $arguments[] = $value;
            $named[$definition->name()] = $value;
        }

        return new ActionArgumentResolution($arguments, $named, $issues);
    }

    /** @return array{0: mixed, 1: ?ActionArgumentIssue} */
    private function resolveDefinition(
        ActionArgumentDefinition $definition,
        RequestInput $input,
        RequestInterface $request,
        ?ExecutionContextInterface $context,
    ): array {
        if ($definition->source() === ActionArgumentSource::Request) {
            return [$request, null];
        }

        if ($definition->source() === ActionArgumentSource::Context) {
            if ($context === null) {
                return [null, $this->issue($definition, 'context.unavailable', 'The execution context is unavailable.')];
            }
            return [$context, null];
        }

        if ($definition->source() === ActionArgumentSource::Service) {
            $identifier = $definition->sourceKey() ?? '';
            if ($this->serviceResolver === null || !$this->serviceResolver->has($identifier)) {
                return [null, $this->issue($definition, 'service.unavailable', 'The requested action service is unavailable.')];
            }
            return [$this->serviceResolver->resolve($identifier), null];
        }

        $inputValue = $input->value($definition->source(), $definition->sourceKey() ?? $definition->name());
        if (!$inputValue->present()) {
            if ($definition->hasDefault()) {
                return [$definition->defaultValue(), null];
            }
            if (!$definition->required()) {
                return [null, null];
            }
            return [null, $this->issue($definition, 'argument.missing', 'A required action argument is missing.')];
        }

        if ($inputValue->isNull()) {
            if ($definition->nullable()) {
                return [null, null];
            }
            return [null, $this->issue($definition, 'argument.null_not_allowed', 'The action argument cannot be null.')];
        }

        return $this->convert($definition, $inputValue);
    }

    /** @return array{0: mixed, 1: ?ActionArgumentIssue} */
    private function convert(ActionArgumentDefinition $definition, RequestInputValue $input): array
    {
        $value = $input->value();
        $converted = match ($definition->type()) {
            ActionArgumentType::Mixed => [true, $value],
            ActionArgumentType::String => $this->toString($value),
            ActionArgumentType::Integer => $this->toInteger($value),
            ActionArgumentType::Float => $this->toFloat($value),
            ActionArgumentType::Boolean => $this->toBoolean($value),
            ActionArgumentType::Array => [is_array($value), is_array($value) ? $value : null],
            ActionArgumentType::Request,
            ActionArgumentType::Context,
            ActionArgumentType::Service => [false, null],
        };

        if (!$converted[0]) {
            return [null, $this->issue(
                $definition,
                'argument.conversion_failed',
                'The action argument could not be converted to the declared type.',
                ['expected_type' => $definition->type()->value],
            )];
        }

        return [$converted[1], null];
    }

    /** @return array{bool, mixed} */
    private function toString(mixed $value): array
    {
        return is_string($value) || is_int($value) || is_float($value) || is_bool($value)
            ? [true, is_bool($value) ? ($value ? 'true' : 'false') : (string) $value]
            : [false, null];
    }

    /** @return array{bool, mixed} */
    private function toInteger(mixed $value): array
    {
        if (is_int($value)) {
            return [true, $value];
        }
        if (is_string($value) && preg_match('/^[+-]?\d+$/', $value) === 1) {
            return [true, (int) $value];
        }
        return [false, null];
    }

    /** @return array{bool, mixed} */
    private function toFloat(mixed $value): array
    {
        if (is_int($value) || is_float($value)) {
            return [true, (float) $value];
        }
        if (is_string($value) && is_numeric($value)) {
            return [true, (float) $value];
        }
        return [false, null];
    }

    /** @return array{bool, mixed} */
    private function toBoolean(mixed $value): array
    {
        if (is_bool($value)) {
            return [true, $value];
        }
        if (is_int($value) && ($value === 0 || $value === 1)) {
            return [true, $value === 1];
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return [true, true];
            }
            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return [true, false];
            }
        }
        return [false, null];
    }

    /** @param array<string, scalar|null> $metadata */
    private function issue(
        ActionArgumentDefinition $definition,
        string $code,
        string $message,
        array $metadata = [],
    ): ActionArgumentIssue {
        return new ActionArgumentIssue($code, $definition->name(), $definition->source(), $message, $metadata);
    }

    /** @param list<ActionArgumentDefinition> $definitions */
    private function usesBody(array $definitions): bool
    {
        foreach ($definitions as $definition) {
            if ($definition->source() === ActionArgumentSource::Body) {
                return true;
            }
        }
        return false;
    }
}
