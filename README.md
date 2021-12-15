# ibm-cos-laravel
 A Laravel package to support the use of IBM COS Api's

## Installation
1. Install package ``composer require kvaksrud/ibm-cos-laravel``
2. Install config ``php artisan vendor:publish --provider=Kvaksrud\IbmCos\IbmCosServiceProvider``

### Set up environment file
Edit ``.env`` file and add these lines
```
IBM_COS_MANAGER_BASE_URI=https://cosmanager.local
IBM_COS_SERVICE_BASE_URI=https://cosmanager.local:8338
IBM_COS_USERNAME=admin
IBM_COS_PASSWORD=Passw0rd
```

if you have different credentials to access the service api you can specify additional properties in the ``.env`` file.
```
IBM_COS_SERVICE_API_USERNAME=serviceadmin
IBM_COS_SERVICE_API_PASSWORD=servicePassw0rd
```
