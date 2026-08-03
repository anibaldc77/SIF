<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\HttpStatus;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\ResponseBody;

final readonly class ApiResponseFactory
{
    public function __construct(
        private ContentNegotiator $negotiator = new ContentNegotiator(),
        private ApiJsonEncoder $encoder = new ApiJsonEncoder(),
    ) {
    }

    public function create(RequestInterface $request, ApiResult $result): ResponseInterface
    {
        $negotiation = $this->negotiator->negotiate(
            $request->headers()->line('Accept'),
            $result->mediaTypes(),
        );

        if (!$negotiation->acceptable()) {
            return $this->notAcceptable($negotiation);
        }

        $selected = $negotiation->selected();
        if ($selected === null) {
            return $this->notAcceptable($negotiation);
        }

        return new Response(
            new HttpStatus($result->status()),
            headers: $result->headers()->with('Content-Type', $selected->value() . '; charset=utf-8'),
            body: new ResponseBody($this->encoder->encode($result->data()), $selected->value(), 'utf-8'),
        );
    }

    /** @param list<MediaType> $supported */
    public function unsupportedMediaType(?string $received, array $supported): ResponseInterface
    {
        return $this->problem(
            415,
            'unsupported_media_type',
            'The request media type is not supported.',
            [
                'received' => $received,
                'supported' => array_map(static fn (MediaType $type): string => $type->value(), $supported),
            ],
        );
    }

    private function notAcceptable(NegotiationResult $result): ResponseInterface
    {
        return $this->problem(
            406,
            'not_acceptable',
            'No acceptable response representation is available.',
            [
                'supported' => array_map(
                    static fn (MediaType $type): string => $type->value(),
                    $result->supported(),
                ),
            ],
        );
    }

    /** @param array<string, mixed> $metadata */
    private function problem(int $status, string $code, string $detail, array $metadata): ResponseInterface
    {
        $payload = [
            'code' => $code,
            'detail' => $detail,
            'metadata' => $metadata,
            'status' => $status,
        ];
        $mediaType = MediaType::problemJson();

        return new Response(
            new HttpStatus($status),
            headers: new \Sif\Foundation\Http\Value\HeaderBag([
                'Content-Type' => $mediaType->value() . '; charset=utf-8',
            ]),
            body: new ResponseBody($this->encoder->encode($payload), $mediaType->value(), 'utf-8'),
        );
    }
}
