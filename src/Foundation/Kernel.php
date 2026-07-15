<?php
declare(strict_types=1);
namespace Sif\Foundation;
use DateTimeImmutable; use Sif\Foundation\Contracts\ApplicationInterface; use Sif\Foundation\Contracts\KernelInterface; use Sif\Foundation\Contracts\LifecycleInterface; use Sif\Foundation\DTO\BootError;
final readonly class Kernel implements KernelInterface
{
    public function __construct(private LifecycleInterface $lifecycle){}
    public function boot(ApplicationInterface $application):BootResult
    {
        $started=new DateTimeImmutable(); try{$application->runtime()->transitionTo(RuntimeState::Bootstrapping,BootStage::Bootstrap);return $this->lifecycle->boot($application);}catch(\Throwable $cause){return $this->failure($application,$cause,$started);}
    }
    public function run(ApplicationInterface $application):BootResult
    {
        $started=new DateTimeImmutable(); if($application->runtime()->isCreated()){$boot=$this->boot($application);if($boot->failed()){return $boot;}}
        try{$application->runtime()->transitionTo(RuntimeState::Running,BootStage::Running);return BootResult::success(BootStage::Running,$started,new DateTimeImmutable());}catch(\Throwable $cause){return $this->failure($application,$cause,$started);}
    }
    public function shutdown(ApplicationInterface $application):BootResult
    {
        $started=new DateTimeImmutable();try{$application->runtime()->transitionTo(RuntimeState::Stopping,BootStage::Shutdown);return $this->lifecycle->shutdown($application);}catch(\Throwable $cause){return $this->failure($application,$cause,$started);}
    }
    private function failure(ApplicationInterface $application,\Throwable $cause,DateTimeImmutable $started):BootResult
    {
        if(!$application->runtime()->hasFailed()&&!$application->runtime()->isStopped()){$application->runtime()->fail($cause,BootStage::Failed);}
        return BootResult::failure(BootStage::Failed,$started,new DateTimeImmutable(),[new BootError('kernel.failure',$cause->getMessage(),BootStage::Failed)],$cause);
    }
}
