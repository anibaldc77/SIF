<?php
declare(strict_types=1);
namespace Sif\Foundation;
use DateTimeImmutable; use Sif\Foundation\Contracts\ApplicationInterface; use Sif\Foundation\Contracts\LifecycleInterface;
final class Lifecycle implements LifecycleInterface
{
    public function bootStages():array{return [BootStage::Environment,BootStage::Bootstrap,BootStage::Providers,BootStage::Booted,BootStage::Running];}
    public function shutdownStages():array{return [BootStage::Shutdown];}
    public function boot(ApplicationInterface $application):BootResult{$started=new DateTimeImmutable();$application->runtime()->transitionTo(RuntimeState::Booted,BootStage::Booted);return BootResult::success(BootStage::Booted,$started,new DateTimeImmutable());}
    public function shutdown(ApplicationInterface $application):BootResult{$started=new DateTimeImmutable();$application->runtime()->transitionTo(RuntimeState::Stopped,BootStage::Shutdown);return BootResult::success(BootStage::Shutdown,$started,new DateTimeImmutable());}
}
