<?php


namespace OneSite\EPay;


/**
 * Interface EPayBankInterface
 * @package OneSite\EPay
 */
interface EPayBankInterface
{
    /**
     * @param $requestId
     * @param $bankId
     * @param $accountNo
     * @param $accountType
     * @param $accountName
     * @return mixed
     */
    public function verifyAccount($requestId, $bankId, $accountNo, $accountType, $accountName);
}
