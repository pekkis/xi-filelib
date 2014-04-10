<?php

namespace Xi\Filelib\Plugin\Image\Adapter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Exception\RuntimeException as ImagineRuntimeException;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Plugin\Image\CommandHelper;

class ImagineImageProcessorAdapter implements ImageProcessorAdapter
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function execute($source, $target, CommandHelper $commandHelper)
    {
        $image = $this->createImage($source);

        foreach ($commandHelper->getCommands() as $command) {
            $image = $command->execute($image);
        }

        $image->save($target);
    }

    /**
     * Creates a new imagick resource from path
     *
     * @param  string                   $path Image path
     * @return ImageInterface
     * @throws InvalidArgumentException
     */
    public function createImage($path)
    {
        try {
            return $this->imagine->open($path);
        } catch (ImagineRuntimeException $e) {
            throw new InvalidArgumentException(
                sprintf("Imagine image could not be created from path '%s'", $path),
                500,
                $e
            );
        }
    }

}
