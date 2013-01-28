<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Operator;

use Xi\Filelib\Configuration;
use Xi\Filelib\Operator\FileOperator;
use Xi\Filelib\Operator\FolderOperator;
use Xi\Filelib\Operator\ResourceOperator;
use Xi\Filelib\Command\CommandFactory;
use Xi\Filelib\Command\Commander;

class OperatorManager
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var FolderOperator
     */
    private $folderOperator;

    /**
     * @var ResourceOperator
     */
    private $resourceOperator;

    public function __construct(
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * @return FileOperator
     */
    public function getFileOperator()
    {
        if (!$this->fileOperator) {
            $this->fileOperator = new FileOperator($this->configuration, $this);
        }

        return $this->fileOperator;
    }

    /**
     * @return FolderOperator
     */
    public function getFolderOperator()
    {
        if (!$this->folderOperator) {
            $this->folderOperator = new FolderOperator($this->configuration, $this);
        }

        return $this->folderOperator;
    }

    /**
     * @return ResourceOperator
     */
    public function getResourceOperator()
    {
        if (!$this->resourceOperator) {
            $this->resourceOperator = new ResourceOperator($this->configuration, $this);
        }

        return $this->resourceOperator;
    }
}
