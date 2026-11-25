<?php

namespace App\Tests\Unit;

use App\Entity\Courrier;
use App\Entity\User;
use App\Service\PieceJointeUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PieceJointeUploaderTest extends TestCase
{
    public function testUploadKeepsFileMetadataAfterMovingTemporaryFile(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'sgec-upload-source-');
        $targetDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sgec-upload-target-'.uniqid();
        self::assertNotFalse($source);
        self::assertNotFalse(file_put_contents($source, 'test attachment content'));
        self::assertTrue(mkdir($targetDirectory));

        try {
            $file = new UploadedFile($source, 'piece-test.pdf', 'application/pdf', UPLOAD_ERR_OK, true);
            $piece = (new PieceJointeUploader($targetDirectory, new AsciiSlugger()))
                ->upload($file, new Courrier(), new User());

            self::assertSame(strlen('test attachment content'), $piece->getTaille());
            self::assertSame('application/pdf', $piece->getTypeMime());
            self::assertFileExists($targetDirectory.DIRECTORY_SEPARATOR.$piece->getNomStockage());
        } finally {
            if (is_file($source)) {
                unlink($source);
            }

            foreach (glob($targetDirectory.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }

            if (is_dir($targetDirectory)) {
                rmdir($targetDirectory);
            }
        }
    }
}
