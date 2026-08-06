<?php

declare(strict_types=1);

use Sif\Foundation\Security\Authorization\AuthorizationAction;
use Sif\Foundation\Security\Authorization\AuthorizationResource;
use Sif\Foundation\Security\Controller\AuthorizationRequirement;

$requirement = new AuthorizationRequirement(
    new AuthorizationAction('document.read'),
    new AuthorizationResource('document', 'example'),
);
