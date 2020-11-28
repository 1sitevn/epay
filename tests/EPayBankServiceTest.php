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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testVerifyAccount()
    {
        $bankId = '970423';
        $accountNo = '1023020330000';
        $accountType = 0;
        $accountName = 'NGUYEN VAN A';

        $account = new EPayBankAccount($accountNo, $accountType, $accountName);

        $data = $this->service->verifyAccount($bankId, $account);

        echo "\n" . json_encode($data);

        return $this->assertTrue(true);
    }

    /**
     *
     */
    public function testTransfer()
    {
        $bankId = '970423';
        $accountNo = '1023020330000';
        $accountType = 0;
        $accountName = 'NGUYEN VAN A';
        $transId = uniqid('9PAY_TEST_TID_' . date('YmdHis') . '_');

        $account = new EPayBankAccount($accountNo, $accountType, $accountName);

        $data = $this->service->transfer($transId, $bankId, $account, 100000, [
            'content' => 'Test'
        ]);

        echo "\n" . json_encode($data);

        return $this->assertTrue(true);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCheckTransaction()
    {
        $data = $this->service->checkTransaction('9PAY_TEST_TID_20201129011201_5fc292f1c23dc');

        echo "\n" . json_encode($data);

        return $this->assertTrue(true);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetBalance()
    {
        $data = $this->service->getBalance();

        echo "\n" . json_encode($data);

        return $this->assertTrue(true);
    }
}
