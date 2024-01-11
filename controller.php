<?php

namespace Concrete\Package\MdBulkFileLocationChanger;

use Concrete\Core\Command\Task\Manager;
use Concrete\Core\Entity\Automation\Task;
use Concrete\Core\Package\Package;
use Doctrine\ORM\EntityManagerInterface;
use Macareux\BulkFileLocationChanger\Controller\BulkFileLocationChangeTaskController;

class Controller extends Package
{
    protected $appVersionRequired = '9.0.0';
    protected $pkgHandle = 'md_bulk_file_location_changer';
    protected $pkgVersion = '1.0.0-alpha.1';
    protected $pkgAutoloaderRegistries = [
        'src' => '\Macareux\BulkFileLocationChanger',
    ];

    public function getPackageName()
    {
        return t('Bulk File Location Changer');
    }

    public function getPackageDescription()
    {
        return t('A Concrete CMS package that allows you to change the location of multiple files at once.');
    }

    public function install()
    {
        $pkg = parent::install();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->app->make(EntityManagerInterface::class);
        $taskHandle = 'md_bulk_file_location_change';
        $task = $entityManager->getRepository(Task::class)->findOneByHandle($taskHandle);
        if (!$task) {
            $task = new Task();
            $task->setHandle($taskHandle);
            $task->setPackage($pkg);
            $entityManager->persist($task);
            $entityManager->flush();
        }
    }

    public function on_start()
    {
        /** @var Manager $manager */
        $manager = $this->app->make(Manager::class);
        $manager->extend('md_bulk_file_location_change', function () {
            return $this->app->make(BulkFileLocationChangeTaskController::class);
        });
    }
}