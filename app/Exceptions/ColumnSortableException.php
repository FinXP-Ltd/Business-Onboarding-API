<?php

namespace App\Exceptions;

use Exception;

class ColumnSortableException extends Exception
{
    public function __construct($message = '', $code = 400, Exception $previous = null)
    {
        switch ($code) {
            default:
            case 0:
                $message = 'Invalid sort argument';
                break;

            case 1:
                $message = 'Relation \''. $message .'\' does not exists.';
                break;

            case 2:
                $message = 'Relation \''. $message .'\' is not instance of HasOne or BelongsTo.';
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}
