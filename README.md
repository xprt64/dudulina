# CQRS + Event Sourcing library for PHP 7+ #

[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)
[![Build Status](https://travis-ci.org/xprt64/cqrs-es.svg?branch=master&rand=2)](https://travis-ci.org/xprt64/cqrs-es)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/quality-score.png?b=master&rand=2)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/coverage.png?b=master&rand=2)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/xprt64/cqrs-es/badges/build.png?b=master&rand=3)](https://scrutinizer-ci.com/g/xprt64/cqrs-es/build-status/master)

This is a non-obtrusive CQRS + Event Sourcing library that helps building complex DDD web applications.

## Features ##

### Minimum dependency on the library in the domain code ###
Only 3 interfaces need to be implemented:
    - `\Gica\Cqrs\Event` for each domain event; no methods, it is just a marker interface; the domain events need to be detected by the automated code generation tools;
    - `\Gica\Cqrs\Command` for each domain command; only one method, `getAggregateId()`; it is needed by the command dispatcher to know that Aggregate instance to load from Repository
    - `\Gica\Cqrs\ReadModel\ReadModelInterface` for each read model; this is required only if you use the `\Gica\Cqrs\ReadModel\ReadModelRecreator` to rebuild your read-models (projections)

