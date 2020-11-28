<?php


namespace OneSite\EPay;


/**
 * Class EPayBankAccount
 * @package OneSite\EPay
 */
class EPayBankAccount
{
    /**
     * @var
     */
    private $accountNo;
    /**
     * @var
     */
    private $accountType;
    /**
     * @var
     */
    private $accountName;

    /**
     * EPayBankAccount constructor.
     * @param $accountNo
     * @param $accountType
     * @param $accountName
     */
    public function __construct($accountNo, $accountType, $accountName)
    {
        $this->accountNo = $accountNo;
        $this->accountType = $accountType;
        $this->accountName = $accountName;
    }

    /**
     * @return mixed
     */
    public function getAccountNo()
    {
        return $this->accountNo;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @return mixed
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

}
