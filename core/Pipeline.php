<?php

declare(strict_types=1);

namespace Core;

class Pipeline
{
    private array $stages = [];

    public function pipe(array $middlewares): static
    {
        $this->stages = $middlewares;
        return $this;
    }

    public function run(Request $request, callable $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->stages),
            fn($next, $middleware) => fn(Request $req) => (new $middleware())->handle($req, $next),
            $destination
        );

        return $pipeline($request);
    }
}
