# CQRS + Event Sourcing library for PHP 7+ #

[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)
[![Build Status](https://travis-ci.org/xprt64/cqrs-es.svg?branch=master&rand=2)](https://travis-ci.org/xprt64/cqrs-es)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/quality-score.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Code Climate](https://codeclimate.com/github/xprt64/cqrs-es/badges/gpa.svg)](https://codeclimate.com/github/xprt64/cqrs-es)
[![Code Coverage](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/coverage.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/build.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/build-status/master)

This is a non-obtrusive CQRS + Event Sourcing library that helps building complex DDD web applications.

## Minimum dependency on the library in the domain code ##
### Only 3 interfaces need to be implemented ###

No inheritance! Your domain code remains clean and infrastructure/framework agnostic as should be.

- `\Gica\Cqrs\Event` for each domain event; no methods, it is just a marker interface; the domain events need to be detected by the automated code generation tools;

- `\Gica\Cqrs\Command` for each domain command; only one method, `getAggregateId()`; it is needed by the command dispatcher to know that Aggregate instance to load from Repository

- `\Gica\Cqrs\ReadModel\ReadModelInterface` for each read model; this is required only if you use the `ReadModelRecreator` to rebuild your read-models (projections)

Even if only a few interfaces need to be implemented, you could loose the coupling to the library even more.
You could define and use your own domain interfaces and only that interfaces would inherit from the library interfaces.
In this way, when you change the library, you change only those interfaces.

## Minimum code duplication on the write side ##

On the write side, you only need to instantiate a command and send it to the `CommandDispatcher`;

Let's create a command.
```php
// immutable and Plain PHP Object (Value Object)
// No inheritance!
class DoSomethingImportantCommand implements Command
{
    private $idOfTheAggregate;
    private $someDataInTheCommand;

    public function __construct($idOfTheAggregate, $someDataInTheCommand)
    {
        $this->idOfTheAggregate = $this->idOfTheAggregate;
        $this->someDataInTheCommand = $this->someDataInTheCommand;
    }

    public function getAggregateId()
    {
        return $this->idOfTheAggregate;
    }

    public function getSomeDataInTheCommand()
    {
        return $this->someDataInTheCommand;
    }
}
```

Now, let's create a simple event:
```php
// immutable, simple object, no inheritance, minimum dependency
class SomethingImportantHappened implements Event
{
    public function __construct($someDataInTheEvent)
    {
        $this->someDataInTheEvent = $someDataInTheEvent;
    }

    public function getSomeDataInTheEvent()
    {
        return $this->someDataInTheEvent;
    }
}
```

Somewhere in the UI or Application layer:
```php

class SomeHttpAction
{
    public function getDoSomethingImportant(RequestInterface $request)
    {
        $idOfTheAggregate = $request->getParsedBody()['id'];
        $someDataInTheCommand = $request->getParsedBody()['data'];

        $this->commandDispatcher->dispatchCommand(new DoSomethingImportantCommand(
            $idOfTheAggregate,
            $someDataInTheCommand
        ));

        return new JsonResponse([
            'success' => 1,
        ]);
    }
}
```

That's it. No transaction management, no loading from the repository, nothing.
The command arrives to the aggregate's command handler, as an argument, like this:
```php
class OurAggregate
{
    //....
    public function handleDoSomethingImportant(DoSomethingImportantCommand $command)
    {
        if($this->outStateDoesNotPermitThis()){
            throw new \Exception("No no, it is not possible!");
        }

        yield new SomethingImportantHappened($command->getSomeDataInTheCommand());
    }

    public function applySomethingImportantHappened(SomethingImportantHappened $event, Metadata $metadata)
    {
        //Metadata is optional
        $this->someNewState = $event->someDataInTheEvent;
    }
}
```

The read models receive the raised event to. They process the event after it is persisted. Take a look at a possible read model:
```php
class SomeReadModel
{
    //...some database initialization, i.e. a MongoDB database injected in the constructor

    public function onSomethingImportantHappened(SomethingImportantHappened $event, Metadata $metadata)
    {
        $this->database->getCollection('ourReadModel')->insertOne([
            '_id' => $metadata->getAggregateId()
            'someData' => $event->getSomeDataInTheEvent()
        ]);
    }

    //this method could be used by the UI to display the data
    public function getSomeDataById($id)
    {
        $document = $this->database->getCollection('ourReadModel')->findOne([
            '_id' => $metadata->getAggregateId()
         ]);

         return $document ? $document['someData'] : null;
    }
}
```
So, when a command is dispatched the following things happen:
- the aggregate class is identified
- the aggregate is loaded from the repository, replaying all previous events
- the command is dispatched to the aggregate instance
- the aggregate yields the events
- the events are persisted to the event store
- the read-models are notified about the new events
- the sagas are notified also; if the sagas generate other commands, the loop is restarted

If an exception is thrown by the command handler on the aggregate, no events are persisted and the exception reach the caller

Read the entire [documentation here](DOCUMENTATION.md)

## Sample application ##
A Todo list sample application can be found at [github.com/xprt64/todosample-cqrs-es](https://github.com/xprt64/todosample-cqrs-es).
