<?php

/**
 * Bzip2CompressionMethod.php
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

namespace CoffeePhp\Bzip2;

use CoffeePhp\Bzip2\Contract\Bzip2CompressionMethodInterface;
use CoffeePhp\Bzip2\Exception\Bzip2CompressException;
use CoffeePhp\Bzip2\Exception\Bzip2UncompressException;
use CoffeePhp\CompressionMethod\AbstractCompressionMethod;
use CoffeePhp\FileSystem\Contract\Data\Path\DirectoryInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\FileInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\PathInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\PathNavigatorInterface;
use CoffeePhp\FileSystem\Contract\FileManagerInterface;
use CoffeePhp\FileSystem\Enum\AccessMode;
use CoffeePhp\FileSystem\Enum\PathConflictStrategy;
use CoffeePhp\Tarball\Contract\TarballCompressionMethodInterface;
use Throwable;

use function bzclose;
use function bzcompress;
use function bzdecompress;
use function bzopen;
use function bzread;
use function bzwrite;
use function is_file;
use function is_string;
use function unlink;

/**
 * Class Bzip2CompressionMethod
 * @package coffeephp\bzip2
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-20
 */
final class Bzip2CompressionMethod extends AbstractCompressionMethod implements Bzip2CompressionMethodInterface
{
    /**
     * @var int
     */
    private const BZIP2_BYTES_TO_READ = 524288; // 512 KiB

    /**
     * @var TarballCompressionMethodInterface
     */
    private TarballCompressionMethodInterface $tarballCompressionMethod;

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
    private int $compressionLevel;

    /**
     * The amount of bytes to read from a file
     * when compressing/uncompressing to/from BZIP2.
     *
     * @var int
     */
    private int $bzip2BytesToRead;

    /**
     * Bzip2CompressionMethod constructor.
     *
     * @param FileManagerInterface $fileManager
     *
     * @param TarballCompressionMethodInterface $tarballCompressionMethod
     *
     * @param PathConflictStrategy|null $pathConflictStrategy
     *
     * @param int $compressionLevel
     * The level of compression.
     * Can be a value ranging from 1 to 9,
     * which decides the block size
     * that is used for compression.
     *
     * Note: This does not affect compression of
     * files/folders, only strings.
     *
     * @param int $bzip2BytesToRead
     * The amount of bytes to read from a file
     * when compressing/uncompressing to/from BZIP2.
     */
    public function __construct(
        FileManagerInterface $fileManager,
        TarballCompressionMethodInterface $tarballCompressionMethod,
        ?PathConflictStrategy $pathConflictStrategy = null,
        int $compressionLevel = self::DEFAULT_COMPRESSION_LEVEL,
        int $bzip2BytesToRead = self::BZIP2_BYTES_TO_READ
    ) {
        parent::__construct($fileManager, $pathConflictStrategy);
        $this->tarballCompressionMethod = $tarballCompressionMethod;
        $this->compressionLevel = $compressionLevel;
        $this->bzip2BytesToRead = $bzip2BytesToRead;
    }

    /**
     * @inheritDoc
     */
    public function compressPath(PathInterface $uncompressedPath): FileInterface
    {
        if ($uncompressedPath->isDirectory()) {
            /** @var DirectoryInterface $uncompressedPath */
            return $this->compressDirectory($uncompressedPath);
        }

        if ($uncompressedPath->isFile()) {
            /** @var FileInterface $uncompressedPath */
            return $this->compressFile($uncompressedPath);
        }

        throw new Bzip2CompressException("The provided path does not exist: {$uncompressedPath}");
    }

    /**
     * @inheritDoc
     */
    public function uncompressPath(FileInterface $compressedPath): PathInterface
    {
        $absolutePath = (string)$compressedPath;

        if ($this->isFullPath($absolutePath, self::EXTENSION_BZIPPED_ARCHIVE)) {
            return $this->uncompressDirectory($compressedPath);
        }

        if ($this->isFullPath($absolutePath, self::EXTENSION_BZIP2)) {
            return $this->uncompressFile($compressedPath);
        }

        throw new Bzip2UncompressException(
            "Failed to uncompress path: {$absolutePath} ; Reason: Unknown file extension provided."
        );
    }

    /**
     * @inheritDoc
     */
    public function compressDirectory(DirectoryInterface $uncompressedDirectory): FileInterface
    {
        try {
            if (!$uncompressedDirectory->exists()) {
                throw new Bzip2CompressException("The given directory does not exist: {$uncompressedDirectory}");
            }
            $archive = $this->tarballCompressionMethod->compressDirectory($uncompressedDirectory);
            $bzippedArchive = $this->compressFile($archive);
            $archive->delete();
            return $bzippedArchive;
        } catch (Bzip2CompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new Bzip2CompressException(
                "Unexpected Compression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function uncompressDirectory(FileInterface $compressedDirectory): DirectoryInterface
    {
        try {
            $absolutePath = (string)$compressedDirectory;
            if (!$compressedDirectory->exists()) {
                throw new Bzip2UncompressException("The given archive does not exist: {$compressedDirectory}");
            }
            $extension = self::EXTENSION_BZIPPED_ARCHIVE;
            if (!$this->isFullPath($absolutePath, $extension)) {
                throw new Bzip2UncompressException(
                    "Directory archive {$absolutePath} does not have the extension: {$extension}"
                );
            }
            $archive = $this->uncompressFile($compressedDirectory);
            $directory = $this->tarballCompressionMethod->uncompressDirectory($archive);
            $archive->delete();
            return $directory;
        } catch (Bzip2UncompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new Bzip2UncompressException(
                "Unexpected Uncompression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function compressFile(FileInterface $file): FileInterface
    {
        try {
            $absolutePath = (string)$file;
            if (!$file->exists()) {
                throw new Bzip2CompressException("The given file does not exist: {$absolutePath}");
            }
            $extension = self::EXTENSION_BZIP2;
            $fullPath = $this->getFullPath($absolutePath, $extension);
            $pathNavigator = $this->getAvailablePath($fullPath);
            return $this->handleLowLevelCompression($file, $pathNavigator);
        } catch (Bzip2CompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new Bzip2CompressException(
                "Unexpected Compression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @param FileInterface $file
     * @param PathNavigatorInterface $destination
     * @return FileInterface
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress UndefinedVariable
     * @psalm-suppress MixedArgument
     */
    private function handleLowLevelCompression(FileInterface $file, PathNavigatorInterface $destination): FileInterface
    {
        try {
            $fileStream = $file->getStream();
            $fileStream->open(AccessMode::READ());
            $bzip2Stream = bzopen((string)$destination, 'w');
            if ($bzip2Stream === false) {
                throw new Bzip2CompressException("Failed to open BZIP2 stream in path: {$destination}");
            }
            foreach ($fileStream->readBytes($this->bzip2BytesToRead) as $chunk) {
                bzwrite($bzip2Stream, $chunk);
            }
            $fileStream->close();
            bzclose($bzip2Stream);
            unset($fileStream, $bzip2Stream);
            return $this->fileManager->getFile($destination);
        } finally {
            if (isset($fileStream) && $fileStream->isOpen()) {
                $fileStream->close();
                unset($fileStream);
            }
            if (isset($bzip2Stream) && $bzip2Stream !== false) { // @phpstan-ignore-line
                bzclose($bzip2Stream);
                unset($bzip2Stream);
                if (is_file((string)$destination)) {
                    unlink((string)$destination);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function uncompressFile(FileInterface $compressedFile): FileInterface
    {
        try {
            $absolutePath = (string)$compressedFile;
            if (!$compressedFile->exists()) {
                throw new Bzip2UncompressException("The given compressed file does not exist: {$absolutePath}");
            }
            $extension = self::EXTENSION_BZIP2;
            if (!$this->isFullPath($absolutePath, $extension)) {
                throw new Bzip2UncompressException(
                    "File {$absolutePath} does not have the extension: {$extension}"
                );
            }
            $originalPath = $this->getOriginalPath($absolutePath, $extension);
            $pathNavigator = $this->getAvailablePath($originalPath);
            return $this->handleLowLevelUncompression($compressedFile, $pathNavigator);
        } catch (Bzip2UncompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new Bzip2UncompressException(
                "Unexpected Uncompression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @param FileInterface $compressedFile
     * @param PathNavigatorInterface $destination
     * @return FileInterface
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress UndefinedVariable
     * @psalm-suppress MixedArgument
     */
    private function handleLowLevelUncompression(
        FileInterface $compressedFile,
        PathNavigatorInterface $destination
    ): FileInterface {
        try {
            $bzip2Stream = bzopen((string)$compressedFile, 'r');
            if ($bzip2Stream === false) {
                throw new Bzip2UncompressException("Failed to open BZIP2 stream in path: {$compressedFile}");
            }
            $file = $this->fileManager->createFile($destination);
            $fileStream = $file->getStream();
            $fileStream->open(AccessMode::APPEND());
            while ($read = bzread($bzip2Stream, $this->bzip2BytesToRead)) {
                $fileStream->append($read, false);
            }
            bzclose($bzip2Stream);
            $fileStream->close();
            unset($bzip2Stream, $fileStream);
            return $file;
        } finally {
            if (isset($bzip2Stream) && $bzip2Stream !== false) { // @phpstan-ignore-line
                bzclose($bzip2Stream);
                unset($bzip2Stream);
            }
            if (isset($fileStream) && $fileStream->isOpen()) { // @phpstan-ignore-line
                $fileStream->close();
                unset($fileStream);
                if (is_file((string)$destination)) {
                    unlink((string)$destination);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function compressString(string $string): string
    {
        $encodedString = bzcompress($string, $this->compressionLevel);
        if (!is_string($encodedString)) {
            throw new Bzip2CompressException("Failed to BZIP2 compress string: {$string}");
        }
        return $encodedString;
    }

    /**
     * @inheritDoc
     */
    public function uncompressString(string $string): string
    {
        $decodedString = bzdecompress($string);
        if (!is_string($decodedString)) {
            throw new Bzip2UncompressException("Failed to BZIP2 uncompress string: {$string}");
        }
        return $decodedString;
    }
}
