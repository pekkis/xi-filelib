<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\File\File;
use Xi\Filelib\Attacher;

/**
 * Linker interface
 *
 * @author pekkis
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
interface Linker extends Attacher
{
    /**
     * Returns link for a version of a file
     *
     * @param  File   $file
     * @param  string $version   Version identifier
     * @param  string $extension Extension
     * @return string Versioned link
     */
    public function getLink(File $file, $version, $extension);
}
