<?php

namespace Kvaksrud\IbmCos;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Kvaksrud\IbmCos\Objects\CosResponse;
use Kvaksrud\IbmCos\Objects\CosStorageAccountMetadata;

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
                'json' => $this->Body
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
     * Get storage account credentials
     *
     * @param string $id ID of Storage Account
     * @return CosResponse
     */
    public function getStorageAccountCredentials(string $id): CosResponse
    {
        $this->Method = 'GET';
        $this->Uri = '/credentials?project_id=' . $id;
        return $this->ClientRequest();
    }

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

    /**
     * Create a Storage Account to hold buckets
     *
     * Return Codes
     * Code 201 - Success
     * Code 409 - Already exists (Conflict)
     *
     * @param string $id Name of the account
     * @param array|CosStorageAccountMetadata|null $metadata An array of CosStorageAccountMetadata objects or a single CosStorageAccountMetadata object is accepted
     * @return CosResponse
     * @throws Exception
     */
    public function createStorageAccount(string $id, array|CosStorageAccountMetadata $metadata = null): CosResponse
    {
        if(is_array($metadata)){
            foreach($metadata as $data){
                if($data instanceof CosStorageAccountMetadata)
                {
                    if(preg_match(Cos::REGEX_STORAGE_ACCOUNT_METADATA,$data->key) === 1) {
                        $this->Headers += ['x-account-meta-' . $data->key => $data->value];
                        continue;
                    } else
                        throw(new Exception("Invalid metadata name supplied",400));
                }
                throw(new Exception("Invalid metadata objects. Must be of type CosStorageAccountMetadata",400));
            }
        }

        if(Cos::isValidStorageAccountId($id) === false)
            throw(new Exception("Invalid Storage Account name",400));

        $this->Method = 'PUT';
        $this->Uri = '/accounts/' . $id;
        return $this->ClientRequest();
    }

    /**
     * Delete a storage account
     *
     * Return Codes
     * Code 404 - Account not found
     * Code 204 - Success
     * Code 409 - Account contains data (Conflict). Delete buckets and credentials first.
     *
     * @param string $id Name of the account
     * @return CosResponse
     * @throws Exception
     */
    public function deleteStorageAccount(string $id): CosResponse
    {
        if(Cos::isValidStorageAccountId($id) === false)
            throw(new Exception("Invalid Storage Account name",400));

        $this->Method = 'DELETE';
        $this->Uri = '/accounts/' . $id;
        return $this->ClientRequest();
    }


    /**
     * Create container (bucket)
     *
     * Return Codes
     * Code 201 - Success
     * Code 400 - Invalid Storage Location | Invalid Container Vault
     * Code 409 - Conflict - Bucket with that name already exists
     *
     * @param string $name
     * @param string $container_vault
     * @param string $storage_account
     * @return CosResponse
     * @throws Exception
     */
    public function createContainer(string $name, string $container_vault, string $storage_account) : CosResponse
    {
        if(Cos::isValidContainerName($name) === false)
            throw(new Exception("Invalid Container name",400));
        if(Cos::isValidContainerVaultName($container_vault) === false)
            throw(new Exception("Invalid Container Vault name",400));
        if(Cos::isValidStorageAccountId($storage_account) === false)
            throw(new Exception("Invalid Storage Account name",400));

        $this->Headers += ['Content-Type' => 'application/json'];
        $this->Body = [
            'storage_location' => $container_vault,
            'service_instance' => $storage_account
        ];
        $this->Method = 'PUT';
        $this->Uri = '/container/' . $name;
        return $this->ClientRequest();

    }

    /**
     * Delete a container
     *
     * Return Codes
     * Code 204 - Success
     * Code 410 - Gone. Getting container failed
     *
     * @param string $name Container Name
     * @return CosResponse
     * @throws Exception
     */
    public function deleteContainer(string $name) : CosResponse
    {
        if(Cos::isValidContainerName($name) === false)
            throw(new Exception("Invalid Container name",400));

        $this->Method = 'DELETE';
        $this->Uri = '/container/' . $name;
        return $this->ClientRequest();
    }

    /**
     * Create a credential for a storage account
     *
     * @param string $id Storage account id
     * @return CosResponse
     * @throws Exception
     */
    public function createStorageAccountCredentials(string $id) : CosResponse
    {
        if(Cos::isValidStorageAccountId($id) === false)
            throw(new Exception("Invalid Storage Account name",400));

        $this->Headers += ['Content-Type' => 'application/json'];
        $this->Body = [
            'credential' => [
                'project_id' => $id,
                'type' => 'ec2'
            ]
        ];
        $this->Method = 'POST';
        $this->Uri = '/credentials';
        return $this->ClientRequest();
    }


    /**
     * Delete credential
     *
     * @param string $id ID of the credential
     * @return CosResponse
     * @throws Exception
     */
    public function deleteStorageAccountCredential(string $id) : CosResponse
    {
        if(Cos::isValidCredentialId($id) === false)
            throw(new Exception("Invalid credential id name",400));

        $this->Method = 'DELETE';
        $this->Uri = '/credentials/' . $id;
        return $this->ClientRequest();
    }

}
