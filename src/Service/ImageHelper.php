<?php
namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class ImageHelper {

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function upload($imageFile, string $imageDir): string
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        $imageFile->move(
            $imageDir,
            $newFilename
        );
        return $newFilename;
    }
}