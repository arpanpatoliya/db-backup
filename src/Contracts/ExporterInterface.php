<?php

namespace Arpanpatoliya\DBBackup\Contracts;

interface ExporterInterface
{
    public function export(): ?string;
}
