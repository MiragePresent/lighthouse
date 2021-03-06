<?php

namespace Tests\Unit\Schema\Directives\Client;

use Tests\TestCase;
use GraphQL\Type\Definition\ResolveInfo;

class ClientDirectiveTest extends TestCase
{
    /**
     * @test
     */
    public function itCanDefineAClientDirective()
    {
        $resolver = addslashes(self::class).'@resolve';
        $schema = '
        directive @filter(key: String = "default value") on FIELD
        
        type Query {
            foo: String @field(resolver: "'.$resolver.'")
        }
        ';
        $query = '
        {
            foo @filter(key: "baz")
        }
        ';
        $result = $this->executeQuery($schema, $query);

        $this->assertSame(['foo' => 'baz'], $result->data);
    }

    public function resolve($root, array $args, $context, ResolveInfo $info)
    {
        $key = collect($info->fieldNodes)->flatMap(function ($node) {
            return collect($node->directives);
        })->filter(function ($directive) {
            return $directive->name->value === 'filter';
        })->flatMap(function ($directive) {
            return collect($directive->arguments);
        })->filter(function ($arg) {
            return $arg->name->value === 'key';
        })->first();

        return $key->value->value;
    }
}
