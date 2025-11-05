<?php

namespace App\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected ?EntityManagerInterface $em = null;
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;

        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $this->em->close();
            $this->em = null;
        }
    }
}
