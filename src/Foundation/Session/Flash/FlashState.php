<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Flash;

enum FlashState: string
{
    case New = 'new';
    case Available = 'available';
    case Consumed = 'consumed';
}
