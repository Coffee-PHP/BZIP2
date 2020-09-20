<?php

/**
 * Bzip2ComponentRegistrar.php
 *
 * Copyright 2020 Danny Damsky
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-20
 */

declare(strict_types=1);

namespace CoffeePhp\Bzip2\Integration;

use CoffeePhp\Bzip2\Bzip2CompressionMethod;
use CoffeePhp\Bzip2\Contract\Bzip2CompressionMethodInterface;
use CoffeePhp\ComponentRegistry\Contract\ComponentRegistrarInterface;
use CoffeePhp\Compress\Contract\CompressorInterface;
use CoffeePhp\Compress\Contract\FileCompressorInterface;
use CoffeePhp\CompressionMethod\Contract\CompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\DirectoryCompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\PathCompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\StringCompressionMethodInterface;
use CoffeePhp\Di\Contract\ContainerInterface;
use CoffeePhp\Uncompress\Contract\UncompressorInterface;

/**
 * Class Bzip2ComponentRegistrar
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-20
 */
final class Bzip2ComponentRegistrar implements ComponentRegistrarInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerInterface $di): void
    {
        if (
            !$di->has(CompressorInterface::class) ||
            !$di->has(UncompressorInterface::class) ||
            !$di->has(StringCompressionMethodInterface::class) ||
            !$di->has(PathCompressionMethodInterface::class) ||
            !$di->has(FileCompressorInterface::class) ||
            !$di->has(DirectoryCompressionMethodInterface::class) ||
            !$di->has(CompressionMethodInterface::class)
        ) {
            $di->bind(CompressorInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(UncompressorInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(StringCompressionMethodInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(PathCompressionMethodInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(FileCompressorInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(DirectoryCompressionMethodInterface::class, Bzip2CompressionMethodInterface::class);
            $di->bind(CompressionMethodInterface::class, Bzip2CompressionMethodInterface::class);
        }
        $di->bind(Bzip2CompressionMethodInterface::class, Bzip2CompressionMethod::class);
        $di->bind(Bzip2CompressionMethod::class, Bzip2CompressionMethod::class);
    }
}
