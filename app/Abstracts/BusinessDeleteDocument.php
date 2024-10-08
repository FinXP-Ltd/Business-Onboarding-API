<?php

namespace App\Abstracts;

use App\Services\AzureStorage\Facades\AzureStorage;
use Illuminate\Http\Response;
use Exception;

abstract class BusinessDeleteDocument extends BusinessAccessorAndMutator
{
    protected object $afterDeleteProcess;

    protected function setAfterDeleteProcess(object $callback): self
    {
        $this->afterDeleteProcess = $callback;

        return $this;
    }

    protected function runAfterDeleteProcess(): void
    {
        $process = $this->afterDeleteProcess;
        $process();
    }

    protected function deleteFile($document, $path, $column = 'file_name', $name = null)
    {
        $fileName = gettype($document?->$column) === 'string' && trim($document?->$column) !== '' ? $document->$column : $name;

        $fileName = str_replace('+', '%20', urlencode($fileName));

        $azurePath = "$path/$fileName";

        $azure = AzureStorage::removeBlobFile($azurePath);

        if($azure['code'] === Response::HTTP_ACCEPTED) {
            if ($this->afterDeleteProcess) {
                $this->runAfterDeleteProcess();
            }
        } else {
            info($azure);
            throw new Exception("Unable to delete file: [file] $fileName, [id] $document?->id");
        }
    }
}
