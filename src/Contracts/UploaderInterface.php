<?php

namespace Arpanpatoliya\DBBackup\Contracts;

interface UploaderInterface
{
    public function upload(string $filePath): bool;
}

