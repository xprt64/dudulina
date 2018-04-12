# Readmodels and Microservices

Can a Readmodel be a microservice? Yes. In fact it is a very good canditate for this.

A Readmodel has two components:
- readmodel-updater; it listen for events and updates the query state (i.e. a database)
- query-service; it responds to client (outside) requests (i.e. HTTP REST)

Each one of these components can be a separate microservice or a single microservice

## Readmodel-updater as a microservice

The Readmodel-updater can be a separate microservice, with only one job: keep the query-able state up-to-date. 
It does this by listening to the events. 

There are two modes in which a Readmodel-updater can run:

- periodically running and polling the Event store
- as a daemon by tailing the Event store

### Running the Readmodel-updater microservice by periodically running and polling the Event store

You can do this using a cron-job:

```
* * * * * /usr/local/bin/php /app/bin/readmodel.php
``` 

The `readmodel.php` script looks like this:

```php
    $readModelTailer = $container->get(\Dudulina\ReadModel\ReadModelTail::class);
    /** @var \Dudulina\ReadModel\ReadModelInterface $readModel */
    $readModel = $container->get(\Some\Read\Model);
    
    /** @var $somePersistentStorage that you own */
    $previousProcessedEvent = $somePersistentStorage->load();
    
    $lastProcessedEventTimestamp = $readModelTailer->pollAndApplyEvents($readModel, $previousProcessedEvent); //does not block
    
    $somePersistentStorage->save($lastProcessedEventTimestamp);
```

If you want to reuse the loaded instances, you could use a while loop combined with a sleep:

```php
    $readModelTailer = $container->get(\Dudulina\ReadModel\ReadModelTail::class);
    /** @var \Dudulina\ReadModel\ReadModelInterface $readModel */
    $readModel = $container->get(\Some\Read\Model);
    
    /** @var $somePersistentStorage that you own */
    $previousProcessedEvent = $somePersistentStorage->load();
    
    while(true) {
        $previousProcessedEvent = $readModelTailer->pollAndApplyEvents($readModel, $previousProcessedEvent); //does not block
        $somePersistentStorage->save($previousProcessedEvent);
        sleep(1);
    }
```

The disadvantage of this method is that it may takes some time until the Readmodel is updated.

### Running the Readmodel-updater microservice by tail-ing the Event store

You can have a realtime-like update of a Readmodel by tailing the Event store.
The `\Dudulina\ReadModel\ReadModelTail` is the class responsible with tailing the Event store and applying the new events 
to the Readmodel. 
Below it is a sample of complete (re)building of a Readmodel, followed by a tail on the Event store:

```php
    $readModelTailer = $container->get(\Dudulina\ReadModel\ReadModelTail::class);
    
    /** @var \Dudulina\ReadModel\ReadModelInterface $readModel */
    $readModel = $container->get(\Some\Read\Model);
    
    $readModel->clearModel();
    $readModel->createModel();
    
    $readModelTailer->tailRead($readModel); //blocks forever

``` 

It is as simple as that.

If you want to be able to restart the Readmodel-updater without a complete rebuild, you need to persist the last 
processed event. You can do this be supplying a `EventProcessedNotifier` to the `tailRead` method and the 
last processed event sequence.

```php
    /** @var $somePersistentStorage that you own */
    $lastEventSequence = $somePersistentStorage->load();

    $recreator->tailRead($readmodel, $lastEventSequence, new class implements EventProcessedNotifier {
        public function onEventProcessed(EventWithMetaData $eventWithMetaData): void
        {
            $somePersistentStorage->save((string)$eventWithMetaData->getMetaData()->getSequence()));
        }
    });
```

You can put this inside a docker container or docker swarm service and you have a microservice.

#### Safely resuming a Readmodel-updater

In order to restart a Readmodel one needs to *remember* the last processed event and to continue from there.
It should be a simple thing, and it is, in most of the cases. 
But there are situations when an event is processed more than once: when the Readmodel processes the event 
(i.e. by inserting/updating some entity) and then the host process is somehow stopped (i.e. a server crash).
In this way, the last processed event sequence is not persisted so it is not remembered. The next time the Readmodel
starts, it will process again the last processed event. This is a problem in many of the cases. There are at least
two solutions:

1. make the entity mutation idempotent; this means for example that a failed attempt to create an existing entity should be *silently* ignored.
2. store the last processed event sequence in the same transaction (in the same place) as the mutation is done and 
ask the Readmodel-updater to get the last processed event sequence. This is the safest possible solution. The con is the 
fact that the Readmodel-updater has an additional responsibility: to remember the last processed event sequence. 

One needs to decide for himself what is the best solution.

### Use other programming languages

If you want to use JavaScript you can use [dudulina-js-connector](https://github.com/xprt64/dudulina-js-connector) 
and run the updater in a nodejs application.

## Readmodel-query-service as a microservice

Once you have the Readmodel-updater running as a separate microservice, you can put a HTTP REST interface in front
of the persistence (the database) and have a Readmodel-query-service, in whatever language you want.

