<?php

/**
 * Bzip2CompressionMethodTest.php
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

namespace CoffeePhp\Bzip2\Test\Unit;

use CoffeePhp\Bzip2\Bzip2CompressionMethod;
use CoffeePhp\FileSystem\Contract\Data\Path\DirectoryInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\FileInterface;
use CoffeePhp\FileSystem\Data\Path\PathNavigator;
use CoffeePhp\FileSystem\FileManager;
use CoffeePhp\Tarball\TarballCompressionMethod;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

/**
 * Class Bzip2CompressionMethodTest
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-20
 * @see Bzip2CompressionMethod
 */
final class Bzip2CompressionMethodTest extends TestCase
{
    private Generator $faker;
    private FileManager $fileManager;
    private Bzip2CompressionMethod $bzip2;
    private DirectoryInterface $testDirectory;
    private FileInterface $testFile;
    private string $uniqueString;

    /**
     * Bzip2CompressionMethodTest constructor.
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
        $this->fileManager = new FileManager();
        $this->bzip2 = new Bzip2CompressionMethod(
            $this->fileManager,
            new TarballCompressionMethod($this->fileManager)
        );
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function setUp(): void
    {
        parent::setUp();
        $testDirectoryPath = (new PathNavigator(__DIR__))->abc();
        $testFilePath = (clone $testDirectoryPath)
            ->def()->ghi()->jkl()->mno()->pqr()->stu()->vwx()->yz()->down('file.txt');
        $this->testDirectory = $this->fileManager->createDirectory($testDirectoryPath);
        $this->testFile = $this->fileManager->createFile($testFilePath);

        // Generate unique string.
        $uniqueString = '';
        for ($i = 0; $i < $this->faker->numberBetween(50, 9000); ++$i) {
            $uniqueString .= $this->faker->realText();
            $uniqueString .= $this->faker->md5;
            $uniqueString .= $this->faker->regexify('[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}');
        }
        $this->uniqueString = $uniqueString;

        $this->testFile->write($this->uniqueString);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->testDirectory->delete();
    }

    /**
     * @see Bzip2CompressionMethod::compressPath()
     * @see Bzip2CompressionMethod::uncompressPath()
     */
    public function testPathCompression(): void
    {
        $bzip2pedDirectory = $this->bzip2->compressPath($this->testDirectory);

        assertSame(
            "{$this->testDirectory}.tar.bz2",
            (string)$bzip2pedDirectory
        );

        $this->testDirectory->delete();

        $this->bzip2->uncompressPath($bzip2pedDirectory);

        assertTrue(
            $this->testDirectory->exists() &&
            $this->testFile->exists() &&
            $this->testFile->read() === $this->uniqueString
        );

        $bzip2pedDirectory->delete();

        $bzip2pedFile = $this->bzip2->compressPath($this->testFile);

        assertSame(
            "{$this->testFile}.bz2",
            (string)$bzip2pedFile
        );

        $this->testFile->delete();

        $this->bzip2->uncompressPath($bzip2pedFile);

        assertTrue($this->testFile->exists() && $this->testFile->read() === $this->uniqueString);

        $bzip2pedFile->delete();
    }

    /**
     * @see Bzip2CompressionMethod::compressDirectory()
     * @see Bzip2CompressionMethod::uncompressDirectory()
     */
    public function testDirectoryCompression(): void
    {
        $bzip2pedDirectory = $this->bzip2->compressDirectory($this->testDirectory);

        assertSame(
            "{$this->testDirectory}.tar.bz2",
            (string)$bzip2pedDirectory
        );

        $this->testDirectory->delete();

        $this->bzip2->uncompressDirectory($bzip2pedDirectory);

        assertTrue(
            $this->testDirectory->exists() &&
            $this->testFile->exists() &&
            $this->testFile->read() === $this->uniqueString
        );

        $bzip2pedDirectory->delete();
    }

    /**
     * @see Bzip2CompressionMethod::compressFile()
     * @see Bzip2CompressionMethod::uncompressFile()
     */
    public function testFileCompression(): void
    {
        $bzip2pedFile = $this->bzip2->compressFile($this->testFile);

        assertSame(
            "{$this->testFile}.bz2",
            (string)$bzip2pedFile
        );

        $this->testFile->delete();

        $this->bzip2->uncompressFile($bzip2pedFile);

        assertTrue($this->testFile->exists() && $this->testFile->read() === $this->uniqueString);

        $bzip2pedFile->delete();
    }

    /**
     * @see Bzip2CompressionMethod::compressString()
     * @see Bzip2CompressionMethod::uncompressString()
     */
    public function testStringCompression(): void
    {
        assertSame(
            $this->uniqueString,
            $this->bzip2->uncompressString(
                $this->bzip2->compressString($this->uniqueString)
            )
        );
    }
}
