<?php

namespace App\Exceptions;

use App\Enums\KYCEntities;
use Exception;
use Throwable;

class KycpResponseException extends Exception
{
    public function __construct($kycp = null, string $message = null, int $code = 400, Throwable|null $previous = null)
    {
        $getMessage = $this->KYCPResponse($kycp);

        parent::__construct("{$message} {$getMessage}", $code, $previous);
    }

    public function KYCPResponse($response)
    {
        $mapping = null;

        if(isset($response) && !$response['Success']) {
            foreach ($response['Entities'] as $field) {
                if ($field['Result'] == 'Error') {
                    $mapping = $this->findGenKey($field['EntityTypeId'], $field['Message']);
                } else {
                    foreach ($field['Entities'] as $compRep) {
                        if ($compRep['Result'] == 'Error') {
                            $mapping = $this->findGenKey($compRep['EntityTypeId'], $compRep['Message']);
                        } else {
                            foreach ($compRep['Entities'] as $otherRoles) {
                                if ($otherRoles['Result'] == 'Error') {
                                $mapping = $this->findGenKey($otherRoles['EntityTypeId'], $otherRoles['Message']);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $mapping;
    }

    public function findGenKey($entityId, $message)
    {
        preg_match("/\bGEN\w*\b/", $message, $match);

        if (isset($match[0])) {
            $resource = $this->getResources();
            $entity = KYCEntities::from($entityId)->word();

            if (isset($resource[$entityId]['fields'][$match[0]]) && $entity) {
                $mapping = $resource[$entityId]['fields'][$match[0]] ?? "NotFoundGenKey";
                return "Please check {$entity} ({$match[0]}) {$mapping} field.";
            }
        }
    }

    private function getResources()
    {
       return include(resource_path('constants/corporate-kycp.php'));
    }
}
