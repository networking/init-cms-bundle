<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Lib;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedBase64File extends UploadedFile
{

    public function __construct(string $base64String, string $originalName, string $mimeType = null)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $data = base64_decode($base64String);
        file_put_contents($filePath, $data);
        $error = null;
        $mimeType = null;
        $test = true;

        parent::__construct($filePath, $originalName, $mimeType, $error, $test);
    }

}
