# Todo sample application for cqrs-es for PHP7 #
This is a sample application to show how [cqrs-es](https://github.com/xprt64/cqrs-es "cqrs-es on github") can be used in production.

## Architecture ##

The application uses a [zend-expressive](https://zendframework.github.io/zend-expressive/ "https://zendframework.github.io/zend-expressive/") skeleton for HTTP bindings, dependency injection and template rendering.

The architecture is based on CQRS and Event Sourcing using [xprt64/cqrs-es](https://github.com/xprt64/cqrs-es "cqrs-es on github") library.

The basic ideea of CQRS with Event Sourcing is that in order to modify the state of the application commands must be executed.
The result of the command are the events that are persisted in an Event Store.
Those events are used to rehydrate the write models and to update the read models (the projections).


###  The Commands ###
The instructions to update application's state are encapsulated in commands, plain PHP objects or Value Objects in DDD.
Commands are sent to Aggregates, the write models. The command must contain at least the Aggregate's ID so that the library can identify the right Aggregate instance.
All commands must implement the `\Gica\Cqrs\Command` interface. This interface has only one method: `getAggregateId()`; This is required for the automated tools to discover command handlers.
An example of a command is this:
```php

use \Gica\Cqrs\Command;

class ImportantCommand implements Command
{
    private $aggregateId;

    public function __constructor($aggregateId)
    {
        $this->aggregateId  = $aggregateId;
    }

    public function getAggregateId() //required method by the interface
    {
        return $this->aggregateId;
    }
}
```
### The Aggregates = write models ###
The receivers of the Commands are Aggregates, more exactly, command handlers, that are methods on the Aggregates.
Aggregates does not contain any dependency to infrastructure or user interface, only to some stateless domain services or other Aggregates IDs.
The internal structure of an Aggregate is hidden from the outside (all the property members are private).
The only public things are Aggregate's command handlers and Aggregate's event *apply*ing methods. Aggregates are not queried.
The only method in which an Aggregate can be interrogated is by listening to it's events.
Aggregates do not inherit and nothing is injected in their constructor, in order to keep them pure.

#### Comand handlers ####
An aggregate command handler has the following signature:
```php
    public function handleTheCommandShortName(TheCommandClass $command)
```

where
 - *TheCommandShortName* is the last part of the command class
 - and  *TheCommandClass* is the type hinted PHP class of the command.

 In a Aggregate's command handler, zero or more events are *yield*-ed (in case of success) or exceptions are thrown (in case of an invariant violation).

 An example of a command handler is this:
 ```php
    public function handleImportantCommand(ImportantCommand $command)
    {
        if($this->stateIsNotOk()){
            throw new \Exception("An invariant prevents this command to be executed");
        }

        yield new \SomethingImportantJustHappened();
    }
 ```

#### Event apply-ing methods ####

After an Aggregate raises an event, that event is applied on the Aggregate itself, in order to update it's state.
Also, when an Aggregate is loaded from the repository, all previous events raised by this instance of the Aggregate (identified by it's ID) are applied to the Aggregate.
In this way, when a command arrives at an Aggregate, that Aggregate has the state already constructed.
An event apply-ing method has the following signature:
```php
    public function applyTheShortEventName(EventClass $event, Metadata $metadata)
```

The apply method update the internal state of the aggregate as it wishes and __DO NOT__ throw any exception.
An example of an apply method is this:
 ```php
    public function applySomethingImportantJustHappened(SomethingImportantJustHappened $event, Metadata $metadata)
    {
        $this->hasHappened = true;
        $this->whenHappened = $metadata->getDateCreated();
    }
```
The `Metadata` is optional and contains the Aggregate's ID, Aggregate's class name, the date of the event creation and the authenticated used id

### The Read Models = The Projections ###
After the Aggregate generates events, these events are sent to the subscribed Read Models.
The Read Models are classes that listen on the events and update themselves accordingly. They have one or more event handler methods.
If you want a Read Model to be recreated by a library tool (`\Gica\Cqrs\ReadModel\ReadModelRecreator`), then the Read Model must implement the `\Gica\Cqrs\ReadModel\ReadModelInterface` interface.
#### Order of event delivery ####
When an event is generated, all Read Models are notified, in the order of their dependencies.
That is, if ReadModel2 depends of ReadModel1, the ReadModel2 receives the event after ReadModel1 processes it.
In this way, ReadModel2 can interogate safely ReadModel1 in order to get some information from it.
This coupling must be used with care as it makes harder the creation of micro-services from a monolit application.
#### Event handler methods ####
These methods have the following signature:
```php
   public function onTheShortEventName(EventClass $event, Metadata $metadata)
```
The `Metadata` is optional and contains the Aggregate's ID, Aggregate's class name, the date of the event creation and the authenticated used id
Event handlers must not throw any exception.

#### Sample Read Model ####
The Read Models are created by an abstract factory and thus can have services injected in the constructor.
One thing that is common to be injected is a type-hinted database connection.
Here is an complete example of a Mongodb backed Read Model:
```php

namespace Domain\Read\Todo;

class TodoList implements ReadModelInterface
{

    /**
     * @var ReadModelsDatabase
     */
    private $database;

    public function __construct(
        ReadModelsDatabase $database
    )
    {
        $this->database = $database;
    }

    private function getCollection()
    {
        return $this->database->selectCollection('TodoList');
    }

    public function clearModel() //required by the interface
    {
        $this->getCollection()->drop();
    }

    public function createModel() //required by the interface
    {
         $this->getCollection()->addIndex(['id' => 1], ['unique' => true]);
    }

    public function onANewTodoWasAdded(ANewTodoWasAdded $event, MetaData $metaData)
    {
        $this->getCollection()->insertOne([
            'id'        => (string)$metaData->getAggregateId(),
            'text'      => $event->getText(),
            'done'      => false,
            'dateAdded' => new UTCDateTime($metaData->getDateCreated()->getTimestamp() * 1000),
        ]);
    }
}
```

### Sagas = Write side event processors ###
After the Aggregate generates events and Read Models are notified about them, these events are sent only once to the subscribed Sagas.
An event is sent to a Saga only after is generated by the Aggregate, thus ensuring that it is processed only once.
A typical Saga is to implement email sending. These Sagas are stateless, that is, when a Saga is instantiated, it's state is not loaded from the repository.
A Saga's event handler has the following signature:
```php
   public function processTheShortEventName(EventClass $event, Metadata $metadata)
```
A Saga's event handler __must not throw any exception__.
A Saga could query the Read Models, as it receives the events after the Read Models process them.
A Saga is instantiated by an abstract factory, so one can inject services into it.

#### Sample Saga ####
Here is an complete example of a Saga:
```php

class SendEmailToClientSaga
{

    private $mailTransport;

    public function __construct(
        MailTransport $mailTransport
    )
    {
        $this->mailTransport = $mailTransport;
    }

    public function processANewUserRegistered(ANewUserRegistered $event, MetaData $metaData)
    {
        $this->mailTransport->sendMail($this->composeEmailTo($event->getEmailAddress()));
    }
}
```