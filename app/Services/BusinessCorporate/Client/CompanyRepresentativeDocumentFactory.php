<?php

namespace App\Services\BusinessCorporate\Client;

use App\Abstracts\BusinessDeleteDocument;
use App\Exceptions\CompanyRepresentativeDocumentException;
use App\Models\CompanyRepresentative;
use App\Models\CompanyRepresentativeDocuments;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompanyRepresentativeDocumentFactory extends BusinessDeleteDocument
{
    protected array $properties = [];

    private ?CompanyRepresentative $companyRepresentative;

    public function setProperties(array $props): self
    {
        $this->properties = $props;

        return $this;
    }

    public function getProperty(string $key = null)
    {
        if (!$key) {
            return $this->properties;
        }

        return $this->properties[$key] ?? null;
    }

    public function setCompanyRepresentative(?CompanyRepresentative $companyRepresentative): self
    {
        $this->companyRepresentative = $companyRepresentative;

        return $this;
    }

    private function getCompanyRepresentative(): ?CompanyRepresentative
    {
        return $this->companyRepresentative;
    }

    public function deleteDocument(): array
    {
        $this->_checkProperties();

        $document = $this->_getCompanyRepresentativeDocument();

        DB::beginTransaction();

        try {

            $index = $this->getProperty('index');

            $path = "apply_corporate/{$this->getBusiness()->companyInformation->id}/company_representative/{$index}";

            $this->setAfterDeleteProcess(function () use ($document) {
                if ($document) {
                    $columnName = $this->getProperty('type');

                    $document->update([
                        $columnName => null,
                        "{$columnName}_size" => null
                    ]);
                }
            })->deleteFile($document, $path, $this->getProperty('type'), $this->getProperty('file_name'));

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            return [
                'data' => [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'status' => 'failed',
                    'message' => 'Unable to delete document!'
                ],
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,

            ];
        }

        return [
            'code' => Response::HTTP_NO_CONTENT,
            'data' => null
        ];
    }

    private function _getCompanyRepresentativeDocument(): ?CompanyRepresentativeDocuments
    {
        $index = $this->getProperty('index');

        return $this->getCompanyRepresentative()
            ?->companyRepresentativeDocument()
            ->whereIndex((int)($index) + 1)
            ->first();
    }

    private function _checkProperties()
    {
        if (empty($this->getProperty())) {
            throw new CompanyRepresentativeDocumentException(
                'Missing request data',
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
