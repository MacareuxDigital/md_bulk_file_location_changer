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
        // @todo: Add required select field for source storage location
        // @todo: Add required select field for destination storage location
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
        // @todo: Get source storage location ID from input
        // @todo: Get destination storage location ID from input

        $app = Application::getFacadeApplication();
        /** @var Connection $connection */
        $connection = $app->make(Connection::class);
        $qb = $connection->createQueryBuilder();
        // @todo: Get file IDs from source storage location

        $batch = Batch::create();
        // @todo: Add \Concrete\Core\File\Command\ChangeFileStorageLocationCommand to batch

        return new BatchProcessTaskRunner($task, $batch, $input, t('Batch process started...'));
    }
}