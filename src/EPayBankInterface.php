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
     * @param EPayBankAccount $account
     * @return mixed
     */
    public function verifyAccount($requestId, $bankId, EPayBankAccount $account);

    /**
     * @param $requestId
     * @param $bankId
     * @param EPayBankAccount $account
     * @param $amount
     * @param array $options
     * @return mixed
     */
    public function transfer($requestId, $bankId, EPayBankAccount $account, $amount, $options = []);
}
