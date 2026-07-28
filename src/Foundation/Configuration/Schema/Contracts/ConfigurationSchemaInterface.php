<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema\Contracts;

use Sif\Foundation\Configuration\Schema\ConfigurationSchemaRule;

interface ConfigurationSchemaInterface
{
    /** @return list<ConfigurationSchemaRule> */
    public function rules(): array;
}
