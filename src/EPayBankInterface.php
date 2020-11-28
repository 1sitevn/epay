<?php


namespace OneSite\EPay;


/**
 * Interface EPayBankInterface
 * @package OneSite\EPay
 */
interface EPayBankInterface
{


    /**
     * @param $bankId
     * @param EPayBankAccount $account
     * @return mixed
     */
    public function verifyAccount($bankId, EPayBankAccount $account);

    /**
     * @param $referenceId
     * @param $bankId
     * @param EPayBankAccount $account
     * @param $amount
     * @param array $options
     * @return mixed
     */
    public function transfer($referenceId, $bankId, EPayBankAccount $account, $amount, $options = []);

    /**
     * @param $referenceId
     * @return mixed
     */
    public function checkTransaction($referenceId);
}
