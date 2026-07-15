<?php
declare(strict_types=1);
namespace Sif\Foundation;
enum BootStage: string { case Created='created'; case Environment='environment'; case Bootstrap='bootstrap'; case Providers='providers'; case Booted='booted'; case Running='running'; case Shutdown='shutdown'; case Failed='failed'; }
