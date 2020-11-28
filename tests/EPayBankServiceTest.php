<?php

namespace OneSite\EPay;


use PHPUnit\Framework\TestCase;

/**
 * Class EPayBankServiceTest
 * @package OneSite\EPay
 * vendor/bin/phpunit --filter testFunction tests/EPayBankServiceTest.php
 */
class EPayBankServiceTest extends TestCase
{

    /**
     * @var EPayBankService
     */
    private $service;

    /**
     *
     */
    public function setUp(): void
    {
        parent::setUp();

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $this->service = new EPayBankService();
    }

    /**
     *
     */
    public function tearDown(): void
    {
        $this->service = null;

        parent::tearDown();
    }

    /**
     * @throws \Exception
     */
    public function testVerifyAccount()
    {
        $requestId = uniqid('9PAY_TEST_RID_' . date('YmdHis') . '_');
        $bankId = '970423';
        $accountNo = '1023020330000';
        $accountType = 0;
        $accountName = 'NGUYEN VAN A';

        $data = $this->service->verifyAccount($requestId, $bankId, $accountNo, $accountType, $accountName);

        echo "\n" . json_encode($data);

        return $this->assertTrue(true);
    }
}
