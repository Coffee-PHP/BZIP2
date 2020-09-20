<?php

/**
 * Bzip2CompressionMethodInterface.php
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

namespace CoffeePhp\Bzip2\Contract;

use CoffeePhp\CompressionMethod\Contract\CompressionMethodInterface;

/**
 * Interface Bzip2CompressionMethodInterface
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-20
 */
interface Bzip2CompressionMethodInterface extends CompressionMethodInterface
{
    /**
     * @var string
     */
    public const EXTENSION_BZIP2 = 'bz2';

    /**
     * @var string
     */
    public const EXTENSION_ARCHIVE = 'tar';

    /**
     * @var string
     */
    public const EXTENSION_BZIPPED_ARCHIVE = self::EXTENSION_ARCHIVE . '.' . self::EXTENSION_BZIP2;

    /**
     * The level of compression.
     * Can be a value ranging from 1 to 9,
     * which decides the block size
     * that is used for compression.
     *
     * Note: This does not affect compression of
     * files/folders, only strings.
     *
     * @var int
     */
    public const DEFAULT_COMPRESSION_LEVEL = 4;
}
