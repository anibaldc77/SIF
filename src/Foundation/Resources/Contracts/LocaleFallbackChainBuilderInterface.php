<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Localization\LocaleFallbackChain;
use Sif\Foundation\Resources\Localization\LocaleIdentifier;

interface LocaleFallbackChainBuilderInterface
{
    public function build(LocaleIdentifier $requested, ?LocaleIdentifier $default = null): LocaleFallbackChain;
}
