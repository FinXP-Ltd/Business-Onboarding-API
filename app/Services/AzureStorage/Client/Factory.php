<?php

namespace App\Services\AzureStorage\Client;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
class Factory
{
    protected $storageName;
    protected $containerName;
    protected $accessKey;
    protected $version;
    protected $contentType;
    protected $currentDate;
    protected $maxAge;

    public function __construct()
    {
        $this->storageName = config('azure-storage.name');
        $this->accessKey = config('azure-storage.key');
        $this->containerName = config('azure-storage.container');
        $this->version = config('azure-storage.version');
        $this->currentDate = gmdate('D, d M Y H:i:s \G\M\T');
        $this->maxAge = 'max-age=3600';
    }

    public function uploadBlob($file, $fileName, $mimeType, $path) {
        try{
            $urlFile = str_replace('+', '%20', urlencode($fileName));
            $destinationURL = "https://$this->storageName.blob.core.windows.net/$this->containerName/$path/$urlFile";
            $urlResource = "/$this->storageName/$this->containerName/$path/$urlFile";
            $fileLen = filesize($file);
            $handle = fopen($file, "r");

            $headers = [
                'Authorization' => $this->generateAuth($urlResource, $fileLen, $mimeType),
                'x-ms-blob-cache-control' => $this->maxAge,
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => $this->currentDate,
                'x-ms-version' => $this->version,
                'Content-Type' => $mimeType,
                'Content-Length' => $fileLen
            ];

            Http::withHeaders($headers)->send('PUT', $destinationURL,[
                'body' => $handle
            ]);

        } catch (Exception $e) {
            info($e);
        }
    }

    private function generateAuth($urlResource, $contentLength, $contentType)
    {
        $stringToSign = "PUT\n\n\n{$contentLength}\n\n{$contentType}\n\n\n\n\n\n\nx-ms-blob-cache-control:{$this->maxAge}\nx-ms-blob-type:BlockBlob\nx-ms-date:$this->currentDate\nx-ms-version:$this->version\n{$urlResource}";
        $hmac = hash_hmac('sha256', $stringToSign, base64_decode($this->accessKey), true);
        $signature = base64_encode($hmac);

        return 'SharedKey ' . $this->storageName . ':' . $signature;
    }

    public function checkFileExist($path, $file, int $count = 0)
    {
        try{
            $extension = $file->getClientOriginalExtension();
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . ($count == 0 ? "." . $extension :  "({$count})." . $extension);
            $urlFile = str_replace('+', '%20', urlencode($filename));
            $destinationURL = "https://$this->storageName.blob.core.windows.net/$this->containerName/$path/$urlFile";

            $headers = [
                'Authorization' => $this->simpleAuth('GET', "/$this->storageName/$this->containerName/$path/$urlFile"),
                'x-ms-blob-cache-control' => $this->maxAge,
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => $this->currentDate,
                'x-ms-version' => $this->version,
            ];

            $response = Http::withHeaders($headers)->send('GET', $destinationURL);

            if($response->getStatusCode() == Response::HTTP_OK){
                $count++;
                return $this->checkFileExist($path, $file, $count);
            }

            return $filename;
        } catch (Exception $e) {
            info($e);
        }
    }

    public function downloadBlobFile($path)
    {
        try{
            $destinationURL = "https://$this->storageName.blob.core.windows.net/$this->containerName/$path";
            $urlResource = "/$this->storageName/$this->containerName/$path";

            $headers =  [
                'Authorization' => $this->simpleAuth('GET', $urlResource),
                'x-ms-blob-cache-control' => $this->maxAge,
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => $this->currentDate,
                'x-ms-version' => $this->version,
            ];

            return Http::withHeaders($headers)->send('GET', $destinationURL)->body();

        } catch (Exception $e) {
            info($e);
        }
    }

    private function simpleAuth($method, $blobName)
    {
        $stringToSign = "$method\n\n\n\n\n\n\n\n\n\n\n\nx-ms-blob-cache-control:$this->maxAge\nx-ms-blob-type:BlockBlob\nx-ms-date:$this->currentDate\nx-ms-version:$this->version\n{$blobName}";
        $hmac = hash_hmac('sha256', $stringToSign, base64_decode($this->accessKey), true);
        $signature = base64_encode($hmac);

        return 'SharedKey ' . $this->storageName . ':' . $signature;
    }

    public function removeBlobFile($path)
    {
        try{
            $destinationURL = "https://$this->storageName.blob.core.windows.net/$this->containerName/$path";
            $urlResource = "/$this->storageName/$this->containerName/$path";

            $headers =  [
                'Authorization' => $this->simpleAuth('DELETE', $urlResource),
                'x-ms-blob-cache-control' => $this->maxAge,
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => $this->currentDate,
                'x-ms-version' => $this->version,
            ];

           $azure = Http::withHeaders($headers)->send('DELETE', $destinationURL);

           return [
                'code' => $azure->getStatusCode(),
                'message' => $azure->getBody()->getContents()
           ];

        } catch (Exception $e) {
            info($e);
        }
    }
}
