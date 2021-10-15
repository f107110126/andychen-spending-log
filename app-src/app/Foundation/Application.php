<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as FoundationApplication;

class Application extends FoundationApplication
{
    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return realpath($this->basePath . DIRECTORY_SEPARATOR . '..');
    }
}
