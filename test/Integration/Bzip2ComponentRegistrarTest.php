<?php

/**
 * Bzip2ComponentRegistrarTest.php
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
 * @since 2020-09-19
 */

declare(strict_types=1);

namespace CoffeePhp\Bzip2\Test\Integration;

use CoffeePhp\Compress\Contract\CompressorInterface;
use CoffeePhp\Compress\Contract\FileCompressorInterface;
use CoffeePhp\CompressionMethod\Contract\CompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\DirectoryCompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\PathCompressionMethodInterface;
use CoffeePhp\CompressionMethod\Contract\StringCompressionMethodInterface;
use CoffeePhp\Di\Container;
use CoffeePhp\FileSystem\Integration\FileSystemComponentRegistrar;
use CoffeePhp\Bzip2\Contract\Bzip2CompressionMethodInterface;
use CoffeePhp\Bzip2\Bzip2CompressionMethod;
use CoffeePhp\Bzip2\Integration\Bzip2ComponentRegistrar;
use CoffeePhp\Tarball\Integration\TarballComponentRegistrar;
use CoffeePhp\Uncompress\Contract\UncompressorInterface;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

/**
 * Class Bzip2ComponentRegistrarTest
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-19
 * @see Bzip2ComponentRegistrar
 */
final class Bzip2ComponentRegistrarTest extends TestCase
{
    /**
     * @see Bzip2ComponentRegistrar::register()
     */
    public function testRegister(): void
    {
        $di = new Container();
        $fileSystemRegistrar = new FileSystemComponentRegistrar();
        $fileSystemRegistrar->register($di);
        $tarballRegistrar = new TarballComponentRegistrar();
        $tarballRegistrar->register($di);
        $registrar = new Bzip2ComponentRegistrar();
        $registrar->register($di);

        assertTrue($di->has(CompressorInterface::class));
        assertTrue($di->has(UncompressorInterface::class));
        assertTrue($di->has(StringCompressionMethodInterface::class));
        assertTrue($di->has(PathCompressionMethodInterface::class));
        assertTrue($di->has(FileCompressorInterface::class));
        assertTrue($di->has(DirectoryCompressionMethodInterface::class));
        assertTrue($di->has(CompressionMethodInterface::class));
        assertTrue($di->has(Bzip2CompressionMethodInterface::class));
        assertTrue($di->has(Bzip2CompressionMethod::class));

        assertInstanceOf(
            Bzip2CompressionMethod::class,
            $di->get(Bzip2CompressionMethod::class)
        );

        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(CompressorInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(UncompressorInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(StringCompressionMethodInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(PathCompressionMethodInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(FileCompressorInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(DirectoryCompressionMethodInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethodInterface::class),
            $di->get(CompressionMethodInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethod::class),
            $di->get(Bzip2CompressionMethodInterface::class)
        );
        assertSame(
            $di->get(Bzip2CompressionMethod::class),
            $di->get(Bzip2CompressionMethod::class)
        );
    }
}
