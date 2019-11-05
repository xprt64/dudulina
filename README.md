# CQRS + Event Sourcing library for PHP 7+ #

[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)
[![Build Status](https://travis-ci.org/xprt64/dudulina.svg?branch=master&rand=2)](https://travis-ci.org/xprt64/dudulina)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/quality-score.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Code Climate](https://codeclimate.com/github/xprt64/cqrs-es/badges/gpa.svg)](https://codeclimate.com/github/xprt64/cqrs-es)
[![Code Coverage](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/coverage.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/build.png?b=master&rand=4)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/build-status/master)

This is a non-obtrusive [CQRS](http://www.cqrs.nu/) + Event Sourcing library that helps building complex DDD web applications.

## Minimum dependency on the library in the domain code ##
### Only 3 interfaces need to be implemented ###

No inheritance! Your domain code remains clean and infrastructure/framework agnostic as should be.

- `\Dudulina\Event` for each domain event; no methods, it is just a marker interface; the domain events need to be detected by the automated code generation tools;

- `\Dudulina\Command` for each domain command; only one method, `getAggregateId()`; it is needed by the command dispatcher to know that Aggregate instance to load from Repository

- `\Dudulina\ReadModel\ReadModelInterface` for each read model; this is required only if you use the `ReadModelRecreator` to rebuild your read-models (projections)

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
        $this->idOfTheAggregate = $idOfTheAggregate;
        $this->someDataInTheCommand = $someDataInTheCommand;
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
        if($this->ourStateDoesNotPermitThis()){
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
The Read-models can be updated in a separate process, in realtime-like (by tailing) or by polling the Event store or even using [JavaScript](https://github.com/xprt64/dudulina-js-connector). Read more here about how you can [keep the Read-model up-to-date](https://github.com/xprt64/dudulina/blob/master/docs/ReadmodelsAndMicroservices.md).
 
So, when a command is dispatched the following things happen:
- the aggregate class is identified
- the aggregate is loaded from the repository, replaying all previous events
- the command is dispatched to the aggregate instance
- the aggregate yields the events
- the events are persisted to the event store
- the read-models are notified about the new events
- the sagas are notified also; if the sagas generate other commands, the loop is started again.

If an exception is thrown by the command handler on the aggregate, no events are persisted and the exception reach the caller

Read the entire [documentation here](DOCUMENTATION.md)

## The Event store
There is a [MongoDB](https://github.com/xprt64/mongolina) implementation of the Event store and a [Restful HTTP API](https://github.com/xprt64/dudulina-eventstore-api) for this Event store if you want to build Read-models in any other languages than PHP.

A [JavaScript connector](https://github.com/xprt64/dudulina-js-connector) is also available. [Here you can find some examples of updating a Read-model in JavaScript](https://github.com/xprt64/dudulina-js-connector/tree/master/sample).

## The Queries

The library can dispatch queries also. The *Askers* ask questions and the *Answerers* answser them. 

The Askers ask question to the `\Dudulina\Query\Asker` and the can receive the answer as return value or as callback on them
 (the method `$this->whenAnsweredXYZ` or the one marked with `@QueryAsker`).
 
 The Answerers answer questions at the `$this->whenAskedXXX` or `@QueryHandler` marked methods. 
 They can also answer a question when they know that the answer has changed and all the askers are notified, by calling `\Dudulina\Query\Answerer::answer()`.

## CQRS bindings
How does the library know what command handler to call when a command is dispatched? 
Or what read models to notify when a new event is published? The answer to all these questions is CQRS bindings.

Long story short, [the tools](https://github.com/xprt64/dudulina/tree/master/bin) analyze the domain code, detect the handlers and build a PHP file with all the bindings as classes.
Then you use those classes to configure the CommandDispatcher. The `create_bindings.php` must be run every time the domain code changes.

```PHP
php -f vendor/xprt64/dudulina/bin/create_bindings.php -- --src="/some/source/directory" --src="/some/other/source/directory" > cqrs_bindings.php
```

Then you need to include the file `create_bindings.php` to your `index.php` usually after `vendors/autoload.php`.

```PHP
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../deploy/cqrs_bindings.php';
```

## Sample application ##
A Todo list sample application can be found at [github.com/xprt64/todosample-cqrs-es](https://github.com/xprt64/todosample-cqrs-es).

## Querying an Aggregate in DDD ##
Read more about how to [query an Aggregate](https://github.com/xprt64/dudulina/blob/master/docs/QueryingAnAggregate.md) in order to test if a command will succeed or not, without actually executing it.

## Questions? ##
Feel free to post to this group: [https://groups.google.com/forum/#!forum/cqrs--event-sourcing-for-php](https://groups.google.com/forum/#!forum/cqrs--event-sourcing-for-php).
