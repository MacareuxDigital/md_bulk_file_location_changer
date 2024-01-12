<?php

namespace Macareux\BulkFileLocationChanger\Controller;

use Concrete\Core\Command\Batch\Batch;
use Concrete\Core\Command\Task\Controller\AbstractController;
use Concrete\Core\Command\Task\Input\Definition\Definition;
use Concrete\Core\Command\Task\Input\Definition\Field;
use Concrete\Core\Command\Task\Input\Definition\SelectField;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\BatchProcessTaskRunner;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\File\Command\ChangeFileStorageLocationCommand;
use Concrete\Core\File\Command\ChangeFileStorageLocationCommandHandler;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\Support\Facade\Application;

class BulkFileLocationChangeTaskController extends AbstractController
{
    public function getName(): string
    {
        return t('Bulk Change File Location');
    }

    public function getDescription(): string
    {
        return t('A task to change the location of multiple files at once.');
    }

    public function getInputDefinition(): ?Definition
    {
        $definition = new Definition();
        $locationSet = [];
        foreach (app()->make(StorageLocationFactory::class)->fetchList() as $location) {
            $locationSet[$location->getID()] = $location->getName();
        }
        $definition->addField(new SelectField(
            'sourceStorageLocationID',
            t('Source Storage Location'),
            t('Select the source storage location.'),
            $locationSet,
            true
        ));
        $definition->addField(new SelectField(
            'destinationStorageLocationID',
            t('Destination Storage Location'),
            t('Select the destination storage location.'),
            $locationSet,
            true
        ));
        $definition->addField(new Field(
            'limit',
            t('Limit'),
            t('Maximum limit of records. Leave it blank, default value is 100.'),
            false
        ));

        return $definition;
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        $limit = (int)$input->getField('limit')?->getValue() ?: 100;
        $sourceStorageLocationID = $input->getField('sourceStorageLocationID')->getValue();
        $destinationStorageLocationID = $input->getField('destinationStorageLocationID')->getValue();

        $app = Application::getFacadeApplication();
        /** @var Connection $connection */
        $connection = $app->make(Connection::class);
        $qb = $connection->createQueryBuilder();
        $files = $qb->select('fID')
            ->from('Files')
            ->where('fslID = :fslID')
            ->setParameter('fslID', $sourceStorageLocationID)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAllAssociative();
        $batch = Batch::create();
        foreach ($files as $file) {
            $batch->add(new ChangeFileStorageLocationCommand($destinationStorageLocationID, $file['fID']));
        }
        return new BatchProcessTaskRunner($task, $batch, $input, t('Batch process started...'));
    }
}