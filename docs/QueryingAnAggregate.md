# Querying an Aggregate in DDD

## Why

We all like to have our domain code well structured by the bounded context and with clear aggregates boundaries.
But, there is a application component that forces us to cross those boundaries: the  UI.
The UI wants to show to the user in a single page (i.e. the home page of our web based application) all kinds of informations,
from multiple bounded contexts and multiple ReadModels. We don't like that, it makes us feel like we haven't identified correctly the boundaries which is the nightmare of the DDD side of me :)

There is another feature that the UI wants that seem to force us break one golden rules of DDD: **do not interogate the Aggregate!**.
Where do we need this? Well, the UI wants to show to the user that a particular command would not succed in the current system state.
One example is to disable a button if an Aggregate is in a state that won't permit a particular command.

## How
We can do this in two ways:

- by extracting the Aggregate's internal algorithm into a Domain service (my fingers hurt when I write this)
- or by creating a new Aggregate query method that resembles the corresponding command method would return a boolean, true if the command would succed and false otherwise

But neither of these methods does conform to the DDD rules and I agree with those rules. Still, we *must* provide this feature to the UI, the client *really wants it*.

We remember one particular characteristic of an Aggregate: it is a side-effect free class. No matter what method I call on an Aggregate, unless I persist the changes in an Application service, no harm is done.
I can call an Aggregate's method 1000 times and nothing will change. After I load an Aggregate, every time I call a method it will give me the same result (it is pure).

So, we can use this characteristic to interogate an Aggregate without changing its source code.

One more thing: it really is forbidden to interogate an Aggregate's state. But we don't interogate its state, we test a command. Aggregates like receiving commands and that's exactly what we do: we send them commands but we don't persist the changes.


So, considering that we have the following PHP code, in an event sourced application:

``` php
class AccountAggregate
{
    /** @var Money */
    private $balance = 0;

    private $accounId;

    public function withdrawMoney(Money $howMuch)
    {
        if($this->balance->greaterThan($howMuch)){ //and possibly more complicated business logic
            yield new MoneyWithdrawn($this->accountId, $howMuch);
        }
        else {
            throw new DomainException('Not enough money');
        }
    }
}

class AccountService
{
    private $repository;

    public function handleWithdrawMoneyFromAccount(WithdrawMoneyFromAccount $command)
    {
        $account = $this->repository->loadAccount($command->getAccountId());

        $events = iterator_to_array($account->withdrawMoney($command->getHowMuch()));

        $this->repository->persistChanges($command->getAccountId(), $events);
    }
}
```

Your actual implementation may vary but the idea is that the `AccountService` from the Application layer load and `AccountAggregate` from
the repository, call a method on it, it collects the changes and then it persist the changes to the repository.

We would add a new method, named `canWithdrawMoneyFromAccount`, like this:

``` php
class AccountService
{
    private $repository;

    public function handleWithdrawMoneyFromAccount(WithdrawMoneyFromAccount $command)
    {
        $account = $this->repository->loadAccount($command->getAccountId());

        $events = iterator_to_array($account->withdrawMoney($command->getHowMuch()));

        $this->repository->persistChanges($command->getAccountId(), $events);
    }

    public function canWithdrawMoneyFromAccount(WithdrawMoneyFromAccount $command):bool
    {
        $account = $this->repository->loadAccount($command->getAccountId());

        try
        {
            iterator_to_array($account->withdrawMoney($command->getHowMuch()));

            return true;
        }
        catch(DomainException $exception)
        {
            return false;
        }

        //please note that we don't persist the changes in case the method is successful
     }

     //or you could have this method, depending on your style
    public function canWithdrawMoneyFromAccount(AccountId $accountId, Money $howMuch):bool
    {
        $account = $this->repository->loadAccount($accountId);

        try
        {
            iterator_to_array($account->withdrawMoney($howMuch)); //we collect the changes (events) but we discard them

            return true;
        }
        catch(DomainException $exception)
        {
            return false;
        }

        //please note that we don't persist the changes in case the method is successful
     }
}
```

You could even detect if a particular method on an Aggregate would have any side effect, you can detect if idempotency is used:

``` php
class AccountService
{
    private $repository;

    // ... some more code

    public function canWithdrawMoneyFromAccount(AccountId $accountId, Money $howMuch):bool
    {
        $account = $this->repository->loadAccount($accountId);

        try
        {
            $events  = iterator_to_array($account->withdrawMoney($howMuch)); //we collect the changes (events) but we discard them

             return !empty($events); //we treat no side effect like an exception - if this is what we want
         }
        catch(DomainException $exception)
        {
            return false;
        }

        //please note that we don't persist the changes in case the method is successful
     }
}
```

Then, in the UI, we can use this like this:

``` php
class SomeControllerOrWhateverUIComponent
{
    public function someAction()
    {
        //...

        $withdrawButtonIsEnabled = $this->accountService->canWithdrawMoneyFromAccount($accountId, $howMuch);

        //...
    }
}
```

## Optimization

One could combine the fastness of the ReadModels and the encapsulation of the Aggregates to create a super-fast, super-scalable ReadModel that contains
the command classes and future command execution statuses as booleans! This works only for simple commands, i.e. commands that don't contains any context dependent parameters,
but in general that's the information that we need. For example:

```php
class AccountFutureCommandStatus
{
    /** @var AccountId */
    private $accountId;

    /** @var bool */
    private $canBeActivated;

    /** @var bool */
    private $canBeDeActivated;
}
```

This ReadModel is updated every time a relevant event is generated.

## Advantages

- Aggregate's encapsulation is protected
- Aggregate does not risk of becoming anemic
- In cases of high availability or high scalability you can replicate the Aggregate's repository along the read-model;
- It is very explicit: "canSomeCommandSuccedInCurrentSystemState?"
- It is fast and scalable as no locking is necessary if the repository uses optimistick locking when persisting changes (as most event stores do).

## Disclaimer

I haven't seen anyone else using or documenting this tactics except me, *probably I haven't read enough or my memory is not helping me*. I'm very pleased by it and somehow proud that it is my original idea.

## Sources

I draw my inspiration from and thanks to Eric Evans, Vaughn Vernon, Greg Young, Martin Fowler, Kent Beck, Uncle Bob and many others :)
