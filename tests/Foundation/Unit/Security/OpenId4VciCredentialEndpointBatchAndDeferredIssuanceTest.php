<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\BatchCredentialIssuanceServiceInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceTransactionPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceTransactionRepositoryInterface;
use Sif\Foundation\Security\Contracts\DeferredCredentialIssuanceServiceInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceResponse;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceResponse;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceTransaction;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\DeferredCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\DeferredCredentialIssuanceResult;

final class OpenId4VciCredentialEndpointBatchAndDeferredIssuanceTest extends TestCase
{
    public function testBatchRequestContainsOneOrMoreIssuanceRequests(): void
    {
        $request = new BatchCredentialIssuanceRequest([
            new CredentialIssuanceRequest('identity-credential'),
            new CredentialIssuanceRequest('address-credential'),
        ]);

        self::assertCount(2, $request->requests());
    }

    public function testBatchResponseCanDetectDeferredItems(): void
    {
        $response = new BatchCredentialIssuanceResponse([
            new CredentialIssuanceResponse('credential-001'),
            new CredentialIssuanceResponse(null, 'transaction-002'),
        ]);

        self::assertCount(2, $response->responses());
        self::assertTrue($response->hasDeferredItems());
    }

    public function testDeferredRequestBindsTransactionToClient(): void
    {
        $request = new DeferredCredentialIssuanceRequest(
            'transaction-001',
            'wallet-client-001'
        );

        self::assertSame('transaction-001', $request->transactionId());
        self::assertSame('wallet-client-001', $request->clientId());
    }

    public function testDeferredResultCanRepresentPendingState(): void
    {
        $result = new DeferredCredentialIssuanceResult(
            false,
            null,
            'credential_pending'
        );

        self::assertFalse($result->ready());
        self::assertNull($result->response());
        self::assertSame('credential_pending', $result->reason());
    }

    public function testIssuanceTransactionKeepsBindingExplicit(): void
    {
        $transaction = new CredentialIssuanceTransaction(
            'transaction-001',
            'wallet-client-001',
            'user-001',
            'identity-credential',
            new DateTimeImmutable('2026-08-10T16:10:00Z')
        );

        self::assertSame('transaction-001', $transaction->transactionId());
        self::assertSame('wallet-client-001', $transaction->clientId());
        self::assertSame('user-001', $transaction->subjectId());
        self::assertSame(
            'identity-credential',
            $transaction->credentialConfigurationId()
        );
    }

    public function testBatchDeferredAndTransactionContractsAreTyped(): void
    {
        $batch = new \ReflectionMethod(
            BatchCredentialIssuanceServiceInterface::class,
            'issueBatch'
        );
        $deferred = new \ReflectionMethod(
            DeferredCredentialIssuanceServiceInterface::class,
            'resolve'
        );

        self::assertSame(
            BatchCredentialIssuanceResponse::class,
            (string) $batch->getReturnType()
        );
        self::assertSame(
            DeferredCredentialIssuanceResult::class,
            (string) $deferred->getReturnType()
        );

        foreach ([
            CredentialIssuanceTransactionRepositoryInterface::class,
            CredentialIssuanceTransactionPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testEndpointLayerRemainsHttpQueueAndStorageNeutral(): void
    {
        foreach ([
            BatchCredentialIssuanceServiceInterface::class,
            DeferredCredentialIssuanceServiceInterface::class,
            CredentialIssuanceTransactionRepositoryInterface::class,
            CredentialIssuanceTransactionPolicyInterface::class,
            BatchCredentialIssuanceRequest::class,
            BatchCredentialIssuanceResponse::class,
            DeferredCredentialIssuanceRequest::class,
            DeferredCredentialIssuanceResult::class,
            CredentialIssuanceTransaction::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('RabbitMQ', $source);
            self::assertStringNotContainsString('Kafka', $source);
        }
    }
}
