<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

final readonly class ScimServiceProviderConfig
{
    public function __construct(
        private ScimFeatureSupport $patch,
        private ScimFeatureSupport $bulk,
        private ScimFeatureSupport $filter,
        private ScimFeatureSupport $changePassword,
        private ScimFeatureSupport $sort,
        private ScimFeatureSupport $etag
    ) {
    }

    public function patch(): ScimFeatureSupport
    {
        return $this->patch;
    }

    public function bulk(): ScimFeatureSupport
    {
        return $this->bulk;
    }

    public function filter(): ScimFeatureSupport
    {
        return $this->filter;
    }

    public function changePassword(): ScimFeatureSupport
    {
        return $this->changePassword;
    }

    public function sort(): ScimFeatureSupport
    {
        return $this->sort;
    }

    public function etag(): ScimFeatureSupport
    {
        return $this->etag;
    }
}
