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
     * @var array|mixed|null
     */
    private $operationDisburse;
    /**
     * @var array|mixed|null
     */
    private $operationCheckTransStatus;
    /**
     * @var array|mixed|null
     */
    private $operationQueryBalance;

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
        $this->operationDisburse = config('epay.bank.operation_disburse');
        $this->operationCheckTransStatus = config('epay.bank.operation_check_trans_status');
        $this->operationQueryBalance = config('epay.bank.operation_query_balance');
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param $bankId
     * @param EPayBankAccount $account
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verifyAccount($bankId, EPayBankAccount $account)
    {
        try {
            $requestId = uniqid($this->partnerCode . '_RID_' . date('YmdHis') . '_');
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
     * @param $referenceId
     * @param $bankId
     * @param EPayBankAccount $account
     * @param $amount
     * @param array $options
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transfer($referenceId, $bankId, EPayBankAccount $account, $amount, $options = [])
    {
        try {
            $requestId = uniqid($this->partnerCode . '_RID_' . date('YmdHis') . '_');
            $requestTime = date('Y-m-d H:i:s', time());

            $rsa = new RSA();
            $rsa->loadKey(file_get_contents($this->privateKeyPath));
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

            $content = !empty($options['content']) ? $options['content'] : '';

            $params = [
                'RequestId' => $requestId,
                'RequestTime' => $requestTime,
                'PartnerCode' => $this->partnerCode,
                'Operation' => $this->operationDisburse,
                'ReferenceId' => $referenceId,
                'BankNo' => $bankId,
                'AccNo' => $account->getAccountNo(),
                'AccType' => $account->getAccountType(),
                'AccountName' => $account->getAccountName(),
                'RequestAmount' => $amount,
                'Memo' => $content,
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
                $data->ReferenceId,
                $data->TransactionId,
                $data->TransactionTime,
                $data->BankNo,
                $data->AccNo,
                $data->AccName,
                $data->AccType,
                $data->RequestAmount,
                $data->TransferAmount
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
     * @param $referenceId
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkTransaction($referenceId)
    {
        try {
            $requestId = uniqid($this->partnerCode . '_RID_' . date('YmdHis') . '_');
            $requestTime = date('Y-m-d H:i:s', time());

            $rsa = new RSA();
            $rsa->loadKey(file_get_contents($this->privateKeyPath));
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

            $params = [
                'RequestId' => $requestId,
                'RequestTime' => $requestTime,
                'PartnerCode' => $this->partnerCode,
                'Operation' => $this->operationCheckTransStatus,
                'ReferenceId' => $referenceId
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
                $data->ReferenceId,
                $data->TransactionId,
                $data->TransactionTime,
                $data->BankNo,
                $data->AccNo,
                $data->AccName,
                $data->AccType,
                $data->RequestAmount,
                $data->TransferAmount
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
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBalance()
    {
        try {
            $requestId = uniqid($this->partnerCode . '_RID_' . date('YmdHis') . '_');
            $requestTime = date('Y-m-d H:i:s', time());

            $rsa = new RSA();
            $rsa->loadKey(file_get_contents($this->privateKeyPath));
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

            $params = [
                'RequestId' => $requestId,
                'RequestTime' => $requestTime,
                'PartnerCode' => $this->partnerCode,
                'Operation' => $this->operationQueryBalance,
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
                $data->PartnerCode,
                $data->CurrentBalance
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
     * @return array
     */
    private function getHeaders()
    {
        return [
            "Content-Type" => "application/json"
        ];
    }

}
