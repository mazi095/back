<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

define( '_ORM_FIXTURES_PATH',__DIR__ . '/Fixtures/ORM');

abstract class WebTestCase extends SymfonyWebTestCase
{
    /**
     * Loads database fixtures.
     *
     * Paths should be relative to tests/Fixtures/ORM.
     *
     * @param array $fixtures List of relative paths to fixtures.
     */
    final public static function loadOrmFixtures(array $fixtures, array $clasesWthoutAoutincrement = [])
    {
        $kernel = self::bootFreshKernel();
        $container =  $kernel->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        foreach ($clasesWthoutAoutincrement as $class ){
            $metadata = $em->getClassMetaData(get_class($class));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
        }

        $container->get('hautelook_alice.doctrine.executor.fixtures_executor')
            ->execute(
                $em,
                $container->get('hautelook_alice.doctrine.orm.loader_generator')->generate(
                    $container->get('hautelook_alice.fixtures.loader'),
                    $container->get('hautelook_alice.alice.fixtures.loader'),
                    $kernel->getBundles(),
                    $kernel->getEnvironment()
                ),
                $container->get('hautelook_alice.doctrine.orm.fixtures_finder')->resolveFixtures(
                    $kernel,
                    array_map(
                        function (string $fixture): string {
                            return sprintf('%s/%s', rtrim(_ORM_FIXTURES_PATH, '/'), ltrim($fixture, '/'));
                        },
                        $fixtures
                    )
                ),
                false,                   // If true, data will not be purged
                function ($message) { }, // Can be used for logging, if needed
                true                     // If true, truncates instead of deleting
            );
    }

    private static function bootFreshKernel()
    {
        static::bootKernel();

        return static::$kernel;
    }
}