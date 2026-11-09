<?php

namespace tests\Dudulina\Event\EventsApplier\EventsApplierOnAggregateTest;

use Dudulina\Attributes\AggregateEventApplier;
use Dudulina\Event;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;

class EventsApplierOnAggregateTest extends \PHPUnit\Framework\TestCase
{
    private function wrap(Event $event): EventWithMetaData
    {
        return new EventWithMetaData($event, new MetaData(1, 'aggregate', new \DateTimeImmutable()));
    }

    public function testAMarkedMethodAppliesTheEventWhateverItsNameIs()
    {
        $aggregate = new MarkedAggregate();

        (new EventsApplierOnAggregate())->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['whenSomethingHappened'], $aggregate->calls);
        $this->assertInstanceOf(MetaData::class, $aggregate->metaData, 'the method receives the metadata too');
    }

    public function testTheNamingConventionStillWorksByDefault()
    {
        $aggregate = new ConventionAggregate();

        (new EventsApplierOnAggregate())->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['applySomethingHappened'], $aggregate->calls);
    }

    public function testAttributeOnlyModeIgnoresTheNamingConvention()
    {
        $conventional = new ConventionAggregate();
        $marked = new MarkedAggregate();
        $sut = new EventsApplierOnAggregate(attributeOnly: true);

        $sut->applyEventsOnAggregate($conventional, [$this->wrap(new SomethingHappened())]);
        $sut->applyEventsOnAggregate($marked, [$this->wrap(new SomethingHappened())]);

        $this->assertSame([], $conventional->calls, 'apply<EventName> without the attribute is ignored');
        $this->assertSame(['whenSomethingHappened'], $marked->calls);
    }

    public function testTheMarkedMethodWinsOverTheConventionalOne()
    {
        $aggregate = new BothAggregate();

        (new EventsApplierOnAggregate())->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['marked'], $aggregate->calls);
    }

    public function testEventsWithoutApplierAreIgnoredAndTheOthersAreApplied()
    {
        $aggregate = new MarkedAggregate();

        (new EventsApplierOnAggregate(attributeOnly: true))->applyEventsOnAggregate($aggregate, [
            $this->wrap(new SomethingElseHappened()),
            $this->wrap(new SomethingHappened()),
        ]);

        $this->assertSame(['whenSomethingHappened'], $aggregate->calls);
    }

    public function testAUnionTypedMethodAppliesEachOfItsEvents()
    {
        $aggregate = new UnionAggregate();

        (new EventsApplierOnAggregate(attributeOnly: true))->applyEventsOnAggregate($aggregate, [
            $this->wrap(new SomethingHappened()),
            $this->wrap(new SomethingElseHappened()),
        ]);

        $this->assertSame(['SomethingHappened', 'SomethingElseHappened'], $aggregate->calls);
    }

    public function testInheritedMarkedMethodsApply()
    {
        $aggregate = new ChildOfMarkedAggregate();

        (new EventsApplierOnAggregate(attributeOnly: true))->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['whenSomethingHappened'], $aggregate->calls);
    }

    public function testWithAMapTheAppliersAreTakenFromTheMapAndNotFoundByReflection()
    {
        $aggregate = new MarkedAggregate();
        // the map is authoritative: it names a method the attribute does not mark, so reflection is certainly not used
        $map = [MarkedAggregate::class => [SomethingHappened::class => 'mappedMethod']];

        (new EventsApplierOnAggregate(attributeOnly: true, appliersMap: $map))->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['mappedMethod'], $aggregate->calls);
    }

    public function testWithAMapAnAggregateOrEventThatIsNotInTheMapGetsNoApplierInAttributeOnlyMode()
    {
        $marked = new MarkedAggregate();
        $conventional = new ConventionAggregate();
        $map = [MarkedAggregate::class => [SomethingElseHappened::class => 'mappedMethod']];

        $sut = new EventsApplierOnAggregate(attributeOnly: true, appliersMap: $map);
        $sut->applyEventsOnAggregate($marked, [$this->wrap(new SomethingHappened())]);
        $sut->applyEventsOnAggregate($conventional, [$this->wrap(new SomethingHappened())]);

        $this->assertSame([], $marked->calls, 'a marked method that is not in the (stale) map is not used: regenerate the map');
        $this->assertSame([], $conventional->calls);
    }

    public function testWithAMapTheNamingConventionIsTheFallbackUnlessAttributeOnly()
    {
        $aggregate = new ConventionAggregate();

        (new EventsApplierOnAggregate(appliersMap: []))->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['applySomethingHappened'], $aggregate->calls);
    }

    public function testTheAnswersAreRememberedAcrossCalls()
    {
        $aggregate = new ConventionAggregate();
        $sut = new EventsApplierOnAggregate();

        $sut->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened()), $this->wrap(new SomethingElseHappened())]);
        $sut->applyEventsOnAggregate($aggregate, [$this->wrap(new SomethingHappened()), $this->wrap(new SomethingElseHappened())]);

        $this->assertSame(['applySomethingHappened', 'applySomethingHappened'], $aggregate->calls, 'events without applier are ignored every time');
    }

    public function testManyEventsOfDifferentAggregateClassesKeepTheirOwnAppliers()
    {
        $marked = new MarkedAggregate();
        $conventional = new ConventionAggregate();
        $sut = new EventsApplierOnAggregate();

        $sut->applyEventsOnAggregate($marked, [$this->wrap(new SomethingHappened())]);
        $sut->applyEventsOnAggregate($conventional, [$this->wrap(new SomethingHappened())]);
        $sut->applyEventsOnAggregate($marked, [$this->wrap(new SomethingHappened())]);

        $this->assertSame(['whenSomethingHappened', 'whenSomethingHappened'], $marked->calls);
        $this->assertSame(['applySomethingHappened'], $conventional->calls);
    }

    public function testAMarkedMethodMustBePublic()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not public');

        (new EventsApplierOnAggregate())->applyEventsOnAggregate(new PrivateMarkedAggregate(), [$this->wrap(new SomethingHappened())]);
    }

    public function testTwoMarkedMethodsCannotApplyTheSameEvent()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('both apply the event');

        (new EventsApplierOnAggregate())->applyEventsOnAggregate(new DuplicateAggregate(), [$this->wrap(new SomethingHappened())]);
    }

    public function testTheFirstParameterMustBeTypedWithAnEventClass()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not typed with an event class');

        (new EventsApplierOnAggregate())->applyEventsOnAggregate(new UntypedAggregate(), [$this->wrap(new SomethingHappened())]);
    }
}

class SomethingHappened implements Event
{
}

class SomethingElseHappened implements Event
{
}

class MarkedAggregate
{
    public array $calls = [];
    public ?MetaData $metaData = null;

    #[AggregateEventApplier]
    public function whenSomethingHappened(SomethingHappened $event, MetaData $metaData)
    {
        $this->calls[] = 'whenSomethingHappened';
        $this->metaData = $metaData;
    }

    public function mappedMethod(SomethingHappened $event)
    {
        $this->calls[] = 'mappedMethod';
    }
}

class ChildOfMarkedAggregate extends MarkedAggregate
{
}

class ConventionAggregate
{
    public array $calls = [];

    public function applySomethingHappened(SomethingHappened $event)
    {
        $this->calls[] = 'applySomethingHappened';
    }
}

class BothAggregate
{
    public array $calls = [];

    public function applySomethingHappened(SomethingHappened $event)
    {
        $this->calls[] = 'convention';
    }

    #[AggregateEventApplier]
    public function marked(SomethingHappened $event)
    {
        $this->calls[] = 'marked';
    }
}

class UnionAggregate
{
    public array $calls = [];

    #[AggregateEventApplier]
    public function either(SomethingHappened|SomethingElseHappened $event)
    {
        $this->calls[] = (new \ReflectionClass($event))->getShortName();
    }
}

class PrivateMarkedAggregate
{
    #[AggregateEventApplier]
    private function whenSomethingHappened(SomethingHappened $event)
    {
    }
}

class DuplicateAggregate
{
    #[AggregateEventApplier]
    public function first(SomethingHappened $event)
    {
    }

    #[AggregateEventApplier]
    public function second(SomethingHappened $event)
    {
    }
}

class UntypedAggregate
{
    #[AggregateEventApplier]
    public function whenSomethingHappened($event)
    {
    }
}
