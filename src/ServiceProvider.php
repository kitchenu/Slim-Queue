<?php

namespace SlimQueue;

use Bernard\Consumer;
use Bernard\EventListener\ErrorLogSubscriber;
use Bernard\EventListener\FailureSubscriber;
use Bernard\Normalizer\EnvelopeNormalizer;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer;
use Bernard\Router\SimpleRouter;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Normalt\Normalizer\AggregateNormalizer;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['queueDispatcher'] = function ($container) {
            $dispatcher = $container['queueEventDispatcher'];
            $producer = new Producer($container['queueFactory'], $dispatcher);
            $dispatcher->addSubscriber(new FailureSubscriber($producer));

            return new Dispatcher($producer, new MessageFactory($container['settings']['queue']['queueName']));
        };

        $container['queueWorker'] = function ($container) {
            $config = $container['settings']['queue'];

            $router = $this->createRouter($container, $config['queueName']);

            $comsumer = new Consumer($router, $container['queueEventDispatcher']);

            return new Worker($comsumer, $container['queueFactory'], $config['queueName'], $config['workerOptions']);
        };

        $container['queueFactory'] = function ($container) {
            $driver = $this->createDriver($container);
            $serializer = $this->createSerializer();

            return new PersistentFactory($driver, $serializer);
        };

        $container['queueEventDispatcher'] = function ($container) {
            $dispatcher = new EventDispatcher();
            $dispatcher->addSubscriber(new ErrorLogSubscriber());

            return $dispatcher;
        };
    }

    public function createRouter(Container $container, $name)
    {
        return new SimpleRouter([
            $name => function (SlimMessage $message) use ($container) {
                $class = $message->getClass();
                (new $class)($container, $message->all());
            }
        ]);
    }

    public function createSerializer()
    {
        return new Serializer(
            new AggregateNormalizer([
                new EnvelopeNormalizer(), new SlimMessageNormalizer()
            ])
        );
    }

    /**
     * 
     * @param type $container
     * @return \Bernard\Driver
     */
    public function crateDriver($container)
    {
        $config = $container['settings']['queue'];
        switch ($config['driver']) {
            case 'appengine':
                return $this->createAppEngineDriver($container[$config['options']['connection']]);

            case 'doctrine':
                return $this->createDoctrineDriver($container[$config['options']['connection']]);

            case 'file':
                return $this->createFlatFileDriver($config['options']);

            case 'phpamqp':
                return $this->createPhpAmqpDriver($config['options'], $container[$config['options']['connection']]);

            case 'phpredis':
                return $this->createPhpRedisDriver($config['options']);

            case 'predis':
                return $this->createPredisDriver($config['options']);

            case 'ironmq':
                return $this->createIronMQDriver($config['options'], $container);

            case 'sqs':
                return $this->createSqsDriver($config['options'], $container);

            case 'pheanstalk':
                return $this->createPheanstalkDriver($config['options'], $container);

            case 'interop':
                return $this->createPheanstalkDriver($config['options'], $container);
        }
    }

    /**
     * 
     * @param array $config
     * @return \Bernard\Driver\AppEngineDriver
     */
    public function createAppEngineDriver(array $config)
    {
        return new \Bernard\Driver\AppEngineDriver($config['queueMap']);
    }    

    /**
     * 
     * @param \Doctrine\DBAL\Connection $connection
     * @return \Bernard\Driver\DoctrineDriver
     */
    public function createDoctrineDriver(\Doctrine\DBAL\Connection $connection)
    {
        $driver = new \Bernard\Driver\DoctrineDriver($connection);

        try {
            $driver->listQueues();
        } catch (\Exception $ex) {
            $schema = new \Doctrine\DBAL\Schema\Schema();

            \Bernard\Doctrine\MessagesSchema::create($schema);

            array_map(array($connection, 'executeQuery'), $schema->toSql($connection->getDatabasePlatform()));
        }

        return $driver;
    }
   
    /**
     * 
     * @param array $config
     * @return \Bernard\Driver\FlatFileDriver
     */
    public function createFlatFileDriver(array $config)
    {
        return new \Bernard\Driver\FlatFileDriver($config['directory']);
    }

    /**
     * 
     * @param array $config
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @return \Bernard\Driver\PhpAmqpDriver
     */
    public function createPhpAmqpDriver(array $config, \PhpAmqpLib\Connection\AMQPStreamConnection $connection)
    {
        return new \Bernard\Driver\PhpAmqpDriver($connection, $config['exchange'], $config['defaultMessageParameters']);
    }

    /**
     * 
     * @param array $config
     * @return \Bernard\Driver\PhpRedisDriver
     */
    public function createPhpRedisDriver(array $config)
    {
        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);
        $redis->setOption(\Redis::OPT_PREFIX, 'bernard:');

        return new \Bernard\Driver\PhpRedisDriver($redis);
    }

    /**
     * 
     * @param array $config
     * @return \Bernard\Driver\PredisDriver
     */
    public function createPredisDriver(array $config)
    {
        $predis = \Predis\Client($config['host'], ['prefix' => 'bernard:']);

        return new \Bernard\Driver\PredisDriver($predis);
    }

    /**
     * 
     * @param array $config
     * @param \IronMQ $ironmq
     * @return \Bernard\Driver\IronMqDriver
     */
    public function createIronMQDriver(array $config, \IronMQ $ironmq)
    {
        return  new \Bernard\Driver\IronMqDriver($ironmq, $config['prefetch']);
    }

    /**
     * 
     * @param array $config
     * @param \MongoClient $mongoClient
     * @return \Bernard\Driver\MongoDBDriver
     */
    public function createMongoDBDriver(array $config, \MongoClient $mongoClient)
    {
        $queues = $mongoClient->selectCollection('bernardDatabase', 'queues');
        $messages = $mongoClient->selectCollection('bernardDatabase', 'messages');
        
        return  new \Bernard\Driver\MongoDBDriver($queues, $messages);
    }

    /**
     * 
     * @param array $config
     * @param \SqsClient $sqs
     * @return \Bernard\Driver\SqsDriver
     */
    public function createSqsDriver(array $config, \SqsClient $sqs)
    {
        return new \Bernard\Driver\SqsDriver($sqs, $config['queueUrls'], $config['prefetch']);
    }

    /**
     * 
     * @param array $config
     * @param \Pheanstalk\Pheanstalk $pheanstalk
     * @return \Bernard\Driver\PheanstalkDriver
     */
    public function createPheanstalkDriver(array $config, \Pheanstalk\Pheanstalk $pheanstalk)
    {
        return new \Bernard\Driver\PheanstalkDriver($pheanstalk);
    }

    /**
     * @param array $config
     * @return \Bernard\Driver\InteropDriver
     */
    public function createInteropDriver(array $config)
    {
        $context = new \Enqueue\Fs\FsConnectionFactory($config['file']);
        
        return new \Bernard\Driver\InteropDriver($context);
    }
}
