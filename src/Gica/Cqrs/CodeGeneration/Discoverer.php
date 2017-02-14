<?php


namespace Gica\Cqrs\CodeGeneration;


interface Discoverer
{
    public function discover(string $searchDirectory);
}