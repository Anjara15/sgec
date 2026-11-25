<?php

namespace App\Service;

use App\Entity\Courrier;
use App\Entity\PieceJointe;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PieceJointeUploader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, Courrier $courrier, User $user): PieceJointe
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $size = $file->getSize();
        $mimeType = $file->getClientMimeType() ?? 'application/octet-stream';

        try {
            $file->move($this->targetDirectory, $newFilename);
        } catch (FileException $e) {
            throw new FileException('Échec du téléversement de la pièce jointe: '.$e->getMessage(), previous: $e);
        }

        return (new PieceJointe())
            ->setNomOriginal($file->getClientOriginalName())
            ->setNomStockage($newFilename)
            ->setTypeMime($mimeType)
            ->setTaille($size ?: 0)
            ->setUtilisateur($user)
            ->setCourrier($courrier);
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
