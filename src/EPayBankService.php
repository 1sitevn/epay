<?php


namespace OneSite\EPay;


use GuzzleHttp\Client;
use phpseclib\Crypt\RSA;

/**
 * Class EPayBankService
 * @package OneSite\EPay
 */
class EPayBankService implements EPayBankInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array|mixed|null
     */
    private $apiUrl;

    /**
     * @var array|mixed|null
     */
    private $privateKeyPath;
    /**
     * @var array|mixed|null
     */
    private $publicKeyPath;
    /**
     * @var array|mixed|null
     */
    private $partnerCode;
    /**
     * @var array|mixed|null
     */
    private $operationVerifyAccount;

    /**
     * EPayBankService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();

        $this->apiUrl = config('epay.bank.api_url');
        $this->privateKeyPath = config('epay.bank.private_key_path');
        $this->publicKeyPath = config('epay.bank.public_key_path');
        $this->partnerCode = config('epay.bank.partner_code');
        $this->operationVerifyAccount = config('epay.bank.operation_verify_account');
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param $requestId
     * @param $bankId
     * @param EPayBankAccount $account
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verifyAccount($requestId, $bankId, EPayBankAccount $account)
    {
        try {
            $requestTime = date('Y-m-d H:i:s', time());

            $rsa = new RSA();
            $rsa->loadKey(file_get_contents($this->privateKeyPath));
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

            $params = [
                'RequestId' => $requestId,
                'RequestTime' => $requestTime,
                'PartnerCode' => $this->partnerCode,
                'Operation' => $this->operationVerifyAccount,
                'BankNo' => $bankId,
                'AccNo' => $account->getAccountNo(),
                'AccType' => $account->getAccountType(),
                'AccountName' => $account->getAccountName(),
            ];

            $params['Signature'] = base64_encode($rsa->sign(implode('|', $params)));

            $response = $this->getClient()->request('POST', $this->apiUrl, [
                'http_errors' => false,
                'verify' => false,
                'headers' => $this->getHeaders(),
                'body' => json_encode($params)
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode != 200) {
                return [
                    'error' => [
                        'message' => 'Có lỗi xảy ra. Vui lòng thử lại.',
                        'status_code' => $statusCode,
                    ],
                    'meta_data' => [
                        'params' => $params,
                    ]
                ];
            }

            $data = json_decode($response->getBody()->getContents());

            $rsa->loadKey(file_get_contents($this->publicKeyPath));

            $isVerify = $rsa->verify(implode('|', [
                $data->ResponseCode,
                $data->ResponseMessage,
                $data->RequestId,
                $data->BankNo,
                $data->AccNo,
                $data->AccType,
                $data->ResponseInfo
            ]), base64_decode($data->Signature));

            if (!$isVerify) {
                return [
                    'error' => [
                        'code' => 103,
                        'message' => 'Chữ ký không chính xác',
                    ]
                ];
            }

            return [
                'data' => $data,
                'meta_data' => [
                    'params' => $params,
                ]
            ];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $requestId
     * @param $bankId
     * @param EPayBankAccount $account
     * @param $amount
     * @param array $options
     * @return mixed|void
     */
    public function transfer($requestId, $bankId, EPayBankAccount $account, $amount, $options = [])
    {
        // TODO: Implement transfer() method.
    }


    /**
     * @return array
     */
    private function getHeaders()
    {
        return [
            "Content-Type" => "application/json"
        ];
    }

}
