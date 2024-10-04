<?php

namespace App\Traits;

use App\Http\Requests\UploadDocumentRequests;
use App\Services\AzureStorage\Facades\AzureStorage;

trait UploadDocumentCorporate
{
    public function companyRepresentativeRequirements($path, $uploadDocumentRequests)
    {
        $data = [
            'index' => $uploadDocumentRequests->index ?? null,
            'file_name' => $uploadDocumentRequests->file_name,
        ];

        $file = $uploadDocumentRequests->file('file');
        $fileExtenstion = $file->extension();
        $mimeType = $file->getMimeType();

        $fileName = $file->getClientOriginalName();
        $data['file_name'] =  $file->getClientOriginalName();
        $data['file_type'] = $fileExtenstion;
        $data['size'] = $this->formatFileSize($file->getSize());

        AzureStorage::uploadBlob($file, $fileName, $mimeType, $path);

        return $data;
    }

    public function companyDeclarationRequirements($path, $uploadDocumentRequests)
    {
        $data = [
            'file_name' => $uploadDocumentRequests->file_name,
        ];

        $file = $uploadDocumentRequests->file('file');
        $fileExtenstion = $file->extension();
        $mimeType = $file->getMimeType();

        $fileName = $file->getClientOriginalName();
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_type'] = $fileExtenstion;
        $data['size'] = $this->formatFileSize($file->getSize());

        AzureStorage::uploadBlob($file, $fileName, $mimeType, $path);

        return $data;
    }

    public function requiredDocumentUpload($path, $uploadDocumentRequests)
    {
        $file = $uploadDocumentRequests->file('file');
        $mimeType = $file->getMimeType();

        $fileName = AzureStorage::checkFileExist($path, $file);

        AzureStorage::uploadBlob($file, $fileName, $mimeType, $path);

        $data['file_name'] = $fileName ?? null;
        $data['file_type'] = $uploadDocumentRequests->column;
        $data['file_size'] = $this->formatFileSize($file->getSize());

        return $data;
    }
}
