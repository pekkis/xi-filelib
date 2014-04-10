<?php

namespace Xi\Filelib\Plugin\Image\Adapter;

use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Plugin\Image\CommandHelper;
use Imagick;
use ImagickException;

class ImagickImageProcessorAdapter implements ImageProcessorAdapter
{
    public function execute($source, $target, CommandHelper $commandHelper)
    {
        $imagick = $this->createImagick($source);

        foreach ($commandHelper->getCommands() as $command) {
            $command->execute($imagick);
        }

        $imagick->writeImage($target);
    }

    /**
     * Creates a new imagick resource from path
     *
     * @param  string                   $path Image path
     * @return Imagick
     * @throws InvalidArgumentException
     */
    public function createImagick($path)
    {
        try {
            return new Imagick($path);
        } catch (ImagickException $e) {
            throw new InvalidArgumentException(
                sprintf("ImageMagick could not be created from path '%s'", $path),
                500,
                $e
            );
        }
    }

}
