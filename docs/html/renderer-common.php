<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\Slugifier;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Authorization\Adapter\SimpleAuthorizationAdapter;
use Xi\Filelib\Authorization\AuthorizationPlugin;
use Xi\Filelib\Plugin\Image\Adapter\ImagineImageProcessorAdapter;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

$AuthorizationAdapter = new SimpleAuthorizationAdapter();
$AuthorizationPlugin = new AuthorizationPlugin($AuthorizationAdapter);
$filelib->addPlugin($AuthorizationPlugin, array('default'));

$AuthorizationAdapter
    ->setFolderWritable(true)
    ->setFileReadableByAnonymous(false)
    ->setFileReadable(true);

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/web/files', '600', '700', 'files'),
    new BeautifurlLinker(
        new Slugifier()
    )
);
$publisher->attachTo($filelib);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin);

$versionPlugin = new VersionPlugin(
    'cinemascope',
    array(
        array('thumbnail', array(new Box(800, 200), ImageInterface::THUMBNAIL_OUTBOUND)),
        // 'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/watermark.png', 'se', 10),
    ),
    'jpg',
    new ImagineImageProcessorAdapter(new Imagine())
);
$filelib->addPlugin($versionPlugin);
