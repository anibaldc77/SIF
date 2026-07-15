<?php
declare(strict_types=1);
namespace Sif\Foundation\DTO;
use JsonSerializable; use Sif\Foundation\BootStage;
final readonly class BootError implements JsonSerializable
{
    /** @param array<string, scalar|null|JsonSerializable> $context */
    public function __construct(private string $code, private string $message, private BootStage $stage, private array $context=[]){ }
    public function code(): string{return $this->code;} public function message(): string{return $this->message;} public function stage(): BootStage{return $this->stage;}
    /** @return array<string, scalar|null|JsonSerializable> */ public function context(): array{return $this->context;}
    /** @return array{code:string,message:string,stage:string,context:array<string, scalar|null|JsonSerializable>} */ public function jsonSerialize(): array{return ['code'=>$this->code,'message'=>$this->message,'stage'=>$this->stage->value,'context'=>$this->context];}
}
