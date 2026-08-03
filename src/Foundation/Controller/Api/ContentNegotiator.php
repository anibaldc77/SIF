<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

final class ContentNegotiator
{
    /** @param list<MediaType> $supported */
    public function negotiate(string $accept, array $supported): NegotiationResult
    {
        $supported = array_values($supported);
        if ($supported === []) {
            return NegotiationResult::rejected([]);
        }

        $accept = trim($accept);
        if ($accept === '') {
            return NegotiationResult::accepted($supported[0], $supported);
        }

        $ranges = $this->parseRanges($accept);
        foreach ($ranges as $range) {
            if ($range['quality'] <= 0.0) {
                continue;
            }
            foreach ($supported as $candidate) {
                if ($range['mediaType']->matches($candidate)) {
                    return NegotiationResult::accepted($candidate, $supported);
                }
            }
        }

        return NegotiationResult::rejected($supported);
    }

    /** @return list<array{mediaType: MediaType, quality: float, order: int}> */
    private function parseRanges(string $accept): array
    {
        $ranges = [];
        foreach (explode(',', $accept) as $order => $part) {
            $segments = array_map('trim', explode(';', trim($part)));
            if ($segments[0] === '') {
                continue;
            }

            try {
                $mediaType = MediaType::parse($segments[0]);
            } catch (\InvalidArgumentException) {
                continue;
            }

            $quality = 1.0;
            foreach (array_slice($segments, 1) as $parameter) {
                if (preg_match('/^q\s*=\s*(0(?:\.\d{0,3})?|1(?:\.0{0,3})?)$/i', $parameter, $matches) === 1) {
                    $quality = (float) $matches[1];
                }
            }
            $ranges[] = ['mediaType' => $mediaType, 'quality' => $quality, 'order' => $order];
        }

        usort(
            $ranges,
            static fn (array $left, array $right): int =>
                $right['quality'] <=> $left['quality']
                ?: $right['mediaType']->specificity() <=> $left['mediaType']->specificity()
                ?: $left['order'] <=> $right['order'],
        );

        return $ranges;
    }
}
