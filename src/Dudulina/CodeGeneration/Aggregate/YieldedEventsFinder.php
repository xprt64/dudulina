<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Aggregate;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Build-time static analysis: which events does an aggregate yield from its command handlers?
 *
 * It reads the source of the handlers (and, through `yield from $this->helper()`, of the helper methods they delegate to) and resolves
 * every yielded value to an event class, as far as it can be known without running the code:
 *  - `yield new SomethingHappened(...)`, also `yield (new SomethingHappened(...))->withOld($x)` when `withOld()` is declared to return self/static;
 *  - `yield $event;` when every assignment of `$event` in the same method is resolvable;
 *  - `yield $this->createEvent(...)` / `yield self::create(...)` when the method is declared to return a (concrete) event class.
 * A yielded value that cannot be resolved is an error: the analysis must never silently skip an event.
 */
class YieldedEventsFinder
{
    private Parser $parser;

    /** @var array<string, Node\Stmt[]> parsed and name-resolved source files */
    private array $sourceFiles = [];

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * @param \ReflectionMethod[] $commandHandlers the command handlers of the aggregate (inherited ones included)
     * @return array<class-string, string[]> event class => where it is yielded ("Class::method (File.php:line)")
     * @throws \LogicException when a yielded value cannot be resolved to an event class
     */
    public function findYieldedBy(\ReflectionClass $aggregate, array $commandHandlers): array
    {
        $yielded = [];
        $problems = [];
        $visited = [];

        foreach ($commandHandlers as $handler) {
            $this->analyze($aggregate, $handler, $yielded, $problems, $visited);
        }

        if ($problems) {
            throw new \LogicException(
                "Cannot tell which events {$aggregate->getName()} yields:\n  " . implode("\n  ", $problems) . "\n" .
                "Yield the event with `yield new TheEvent(...)`, or declare the concrete event class as the return type of the method that creates it."
            );
        }

        return $yielded;
    }

    private function analyze(\ReflectionClass $aggregate, \ReflectionMethod $method, array &$yielded, array &$problems, array &$visited): void
    {
        $id = $method->getDeclaringClass()->getName() . '::' . $method->getName();

        if (isset($visited[$id])) {
            return;
        }
        $visited[$id] = true;

        $node = $this->findMethodNode($method);

        if (null === $node) {
            $problems[] = "{$id}: the source of the method cannot be read";
            return;
        }

        foreach ($this->findYields($node) as $yield) {
            $where = $method->getDeclaringClass()->getShortName() . '::' . $method->getName() . ' (' . basename((string)$method->getFileName()) . ':' . $yield->getStartLine() . ')';

            if ($yield instanceof Node\Expr\YieldFrom) {
                $callee = $this->findDelegatedMethod($aggregate, $yield->expr);

                if (null === $callee) {
                    $problems[] = "$where: `yield from` something other than a method of the aggregate ({$this->describe($yield->expr)})";
                    continue;
                }

                $this->analyze($aggregate, $callee, $yielded, $problems, $visited);
                continue;
            }

            $eventClasses = null === $yield->value ? [] : $this->resolveClasses($yield->value, $aggregate, $node);

            if (!$eventClasses) {
                $problems[] = "$where: cannot resolve the yielded " . ($yield->value ? $this->describe($yield->value) : 'nothing');
                continue;
            }

            foreach ($eventClasses as $eventClass) {
                $yielded[$eventClass][] = $where;
            }
        }
    }

    /** @return array<Node\Expr\Yield_|Node\Expr\YieldFrom> the yields of the method body (not those of the closures inside it) */
    private function findYields(Node\Stmt\ClassMethod $method): array
    {
        $visitor = new class extends NodeVisitorAbstract {
            public array $yields = [];

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction || $node instanceof Node\Stmt\Function_
                    || ($node instanceof Node\Stmt\Class_ && $node->isAnonymous())) {
                    return NodeVisitor::DONT_TRAVERSE_CHILDREN;
                }
                if ($node instanceof Node\Expr\Yield_ || $node instanceof Node\Expr\YieldFrom) {
                    $this->yields[] = $node;
                }
                return null;
            }
        };

        (new NodeTraverser($visitor))->traverse($method->stmts ?? []);

        return $visitor->yields;
    }

    /** `yield from $this->helper()`, `yield from self::helper()`, `yield from parent::handle()` */
    private function findDelegatedMethod(\ReflectionClass $aggregate, Node\Expr $expr): ?\ReflectionMethod
    {
        if ($expr instanceof Node\Expr\MethodCall && $expr->var instanceof Node\Expr\Variable && 'this' === $expr->var->name && $expr->name instanceof Node\Identifier) {
            return $aggregate->hasMethod($expr->name->toString()) ? $aggregate->getMethod($expr->name->toString()) : null;
        }

        if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
            $class = match (strtolower($expr->class->toString())) {
                'self', 'static' => $aggregate,
                'parent' => $aggregate->getParentClass() ?: null,
                default => $expr->class->toString() === $aggregate->getName() ? $aggregate : null,
            };

            return $class && $class->hasMethod($expr->name->toString()) ? $class->getMethod($expr->name->toString()) : null;
        }

        return null;
    }

    /** @return string[] the (concrete) classes the expression can evaluate to; empty = unknown */
    private function resolveClasses(Node\Expr $expr, \ReflectionClass $aggregate, Node\Stmt\ClassMethod $method): array
    {
        if ($expr instanceof Node\Expr\New_) {
            return $expr->class instanceof Node\Name ? array_filter([$this->nameToClass($expr->class, $aggregate)]) : [];
        }

        if ($expr instanceof Node\Expr\Variable && is_string($expr->name)) {
            return $this->resolveVariable($expr->name, $aggregate, $method);
        }

        if ($expr instanceof Node\Expr\MethodCall && $expr->name instanceof Node\Identifier) {
            $targets = ($expr->var instanceof Node\Expr\Variable && 'this' === $expr->var->name)
                ? [$aggregate->getName()]
                : $this->resolveClasses($expr->var, $aggregate, $method);

            return $this->returnTypesOf($targets, $expr->name->toString());
        }

        if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
            $target = $this->nameToClass($expr->class, $aggregate);

            return $target ? $this->returnTypesOf([$target], $expr->name->toString()) : [];
        }

        return [];
    }

    /** all the assignments of the variable in the method must be resolvable */
    private function resolveVariable(string $name, \ReflectionClass $aggregate, Node\Stmt\ClassMethod $method): array
    {
        $visitor = new class($name) extends NodeVisitorAbstract {
            public array $values = [];

            public function __construct(private string $name)
            {
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\Variable && $node->var->name === $this->name) {
                    $this->values[] = $node->expr;
                }
                return null;
            }
        };
        (new NodeTraverser($visitor))->traverse($method->stmts ?? []);

        if (!$visitor->values) {
            return [];
        }

        $classes = [];
        foreach ($visitor->values as $value) {
            $resolved = $this->resolveClasses($value, $aggregate, $method);
            if (!$resolved) {
                return [];
            }
            $classes = array_merge($classes, $resolved);
        }

        return array_values(array_unique($classes));
    }

    /** @param string[] $targets @return string[] */
    private function returnTypesOf(array $targets, string $methodName): array
    {
        $classes = [];

        foreach ($targets as $target) {
            if (!class_exists($target) || !method_exists($target, $methodName)) {
                return [];
            }

            $type = (new \ReflectionMethod($target, $methodName))->getReturnType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                return [];
            }

            $class = in_array($type->getName(), ['self', 'static'], true) ? $target : $type->getName();

            if (!class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                return []; // an interface or an abstract class does not say which event it is
            }

            $classes[] = $class;
        }

        return array_values(array_unique($classes));
    }

    private function nameToClass(Node\Name $name, \ReflectionClass $aggregate): ?string
    {
        return match (strtolower($name->toString())) {
            'self', 'static' => $aggregate->getName(),
            'parent' => $aggregate->getParentClass() ? $aggregate->getParentClass()->getName() : null,
            default => $name->toString(),
        };
    }

    private function findMethodNode(\ReflectionMethod $method): ?Node\Stmt\ClassMethod
    {
        $file = $method->getFileName();

        if (false === $file) {
            return null;
        }

        $visitor = new class($method->getName(), $method->getEndLine()) extends NodeVisitorAbstract {
            public ?Node\Stmt\ClassMethod $found = null;

            public function __construct(private string $name, private int $endLine)
            {
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === $this->name && $node->getEndLine() === $this->endLine) {
                    $this->found = $node;
                }
                return null;
            }
        };

        (new NodeTraverser($visitor))->traverse($this->parse($file));

        return $visitor->found;
    }

    /** @return Node\Stmt[] */
    private function parse(string $file): array
    {
        if (!isset($this->sourceFiles[$file])) {
            $statements = $this->parser->parse((string)file_get_contents($file)) ?? [];
            $this->sourceFiles[$file] = (new NodeTraverser(new NameResolver()))->traverse($statements);
        }

        return $this->sourceFiles[$file];
    }

    private function describe(Node\Expr $expr): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', (new \ReflectionClass($expr))->getShortName())) . ' expression on line ' . $expr->getStartLine();
    }
}
