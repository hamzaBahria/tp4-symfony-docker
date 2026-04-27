<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if ($this->shouldUseSystemTempDir()) {
            return $this->getSystemTempSubdirectory('cache').'/'.$this->environment;
        }

        return parent::getCacheDir();
    }

    public function getBuildDir(): string
    {
        if ($this->shouldUseSystemTempDir()) {
            return $this->getSystemTempSubdirectory('build').'/'.$this->environment;
        }

        return parent::getBuildDir();
    }

    private function shouldUseSystemTempDir(): bool
    {
        return \PHP_OS_FAMILY !== 'Windows'
            && str_starts_with($this->getProjectDir(), '/var/www/');
    }

    private function getSystemTempSubdirectory(string $type): string
    {
        return rtrim(sys_get_temp_dir(), '/\\').'/symfony-app/'.$type;
    }
}
