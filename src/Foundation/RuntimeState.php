<?php
declare(strict_types=1);
namespace Sif\Foundation;
enum RuntimeState: string { case Created='created'; case Bootstrapping='bootstrapping'; case Booted='booted'; case Running='running'; case Stopping='stopping'; case Stopped='stopped'; case Failed='failed'; }
