<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\PostTooLargeException;

class Handler extends Exception
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof PostTooLargeException) {
            return back()->withErrors(['paper_file' => 'The uploaded file is too large. Maximum allowed size is 5MB.'])->withInput();
        }
    
        return parent::render($request, $exception);
    }
}
