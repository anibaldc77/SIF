<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

enum ActionArgumentSource: string
{
    case Route = 'route';
    case Query = 'query';
    case Body = 'body';
    case Header = 'header';
    case Cookie = 'cookie';
    case Attribute = 'attribute';
    case Request = 'request';
    case Context = 'context';
    case Service = 'service';
}
