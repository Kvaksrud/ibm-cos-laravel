<?php

namespace Kvaksrud\IbmCos;



use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Kvaksrud\IbmCos\Objects\CosResponse;

class ServiceApi {

    private $Client;
    private $Auth;
    private $Headers;

    public $LastResponse;

    private $Body;
    private $Method;
    private $Uri;

    public function __construct()
    {
        $this->resetClient();
        $this->resetResponse();
        $this->loadAuth();
        $this->resetHeaders();
        $this->Client = new Client([
            'verify' => false,
            'auth' => $this->Auth,
            'base_uri' => config('ibm-cos.service.base_uri'),
            'defaults'=> [
                'headers' => $this->Headers
            ]
        ]);
    }

    private function loadAuth()
    {
        $this->Auth = config('ibm-cos.manager.auth');
    }

    private function resetHeaders(): void
    {
        $this->Headers = [
            'User-Agent' => 'Kvaksrud-IbmCos/Dev',
            'Accept'     => 'application/json'
        ];
    }

    private function resetResponse()
    {
        $this->LastResponse = null;
    }

    private function loadSuccessResponse(Response $response){
        $this->LastResponse = new CosResponse(
            true,
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents()),
            $response->getHeaders(),
            null);
    }

    private function loadClientExceptionResponse(ClientException $response){
        $this->LastResponse = new CosResponse(
            false,
            $response->getCode(),
            json_decode($response->getResponse()->getBody()->getContents()),
            null,
            $response->getMessage());
    }

    private function loadGuzzleExceptionResponse(GuzzleException $response){
        $this->LastResponse = new CosResponse(
            false,
            $response->getCode(),
            null,
            null,
            $response->getMessage());
    }

    private function resetClient()
    {
        $this->Body = null;
        $this->Method = null;
        $this->Uri = null;
    }

    private function ClientRequest()
    {
        try {
            $response = $this->Client->request($this->Method,$this->Uri,[
                'headers' => $this->Headers,
                'body' => $this->Body
            ]);
            $this->loadSuccessResponse($response);
        } catch (ClientException $e){
            $this->loadClientExceptionResponse($e);
        } catch(GuzzleException $e){
            $this->loadGuzzleExceptionResponse($e);
        }
        $this->resetClient();
        $this->resetHeaders();
        return $this->LastResponse;
    }

    /**
     * CONTAINERS
     * Also known as buckets to the normal guy
     */

    /**
     * @param string $container Container name
     * @return CosResponse
     */
    public function getContainer(string $container): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/container/' . $container;
        return $this->ClientRequest();
    }

    /**
     * CREDENTIALS
     * Credentials belonging to Storage Accounts
     */

    /**
     * @param string $id ID of Storage Account
     * @return CosResponse
     */
    public function getCredentials(string $id): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/credentials?project_id=' . $id;
        return $this->ClientRequest();
    }


    /**
     * STORAGE ACCOUNTS
     * Has Containers and Credentials
     */

    /**
     * Get all storage accounts in COS
     * @return CosResponse
     */
    public function getStorageAccounts(): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/accounts';
        return $this->ClientRequest();
    }

    /**
     * Get specific storage accounts in COS
     * @param string $id ID of the COS Storage Account
     * @return CosResponse
     */
    public function getStorageAccount(string $id): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/accounts/' . $id;
        return $this->ClientRequest();
    }

    /**
     * Get specific storage accounts in COS
     * @param string $id ID of the COS Storage Account
     * @return CosResponse
     */
    public function getStorageAccountContainers(string $id): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/accounts/' . $id . '/containers';
        return $this->ClientRequest();
    }


}
