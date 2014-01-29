<?php

namespace Xi\Filelib\Queue;

use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\Result;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;

class FilelibMessageHandler implements MessageHandler, Attacher
{
    private $handledMessages = array(
        'xi_filelib.command.file.after_upload',
        'xi_filelib.command.file.copy',
        'xi_filelib.command.file.delete',
        'xi_filelib.command.file.update',
        'xi_filelib.command.file.upload',
        'xi_filelib.command.folder.create_by_url',
        'xi_filelib.command.folder.create',
        'xi_filelib.command.folder.delete',
        'xi_filelib.command.folder.update',
    );

    /**
     * @var FileLibrary
     */
    private $filelib;

    public function attachTo(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    public function willHandle(Message $message)
    {
        return in_array($message->getType(), $this->handledMessages);
    }

    public function handle(Message $message)
    {
        switch ($message->getType()) {

            case 'xi_filelib.command.file.after_upload':
                $data = $message->getData();
                $file = $this->filelib->getFileOperator()->find($data['file_id']);
                $command = new AfterUploadFileCommand($file);
                $command->attachTo($this->filelib);
                $command->execute();
                $result = new Result(true);
                break;

        }

        return $result;
    }
}
