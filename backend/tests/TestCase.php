<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Console\CliDumper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * DB のテーブルに入っているデータを出力
     */
    protected function dumpdb(): void
    {
        if (class_exists(CliDumper::class)) {
            CliDumper::resolveDumpSourceUsing(fn () => null);
        }

        foreach (Schema::getAllTables() as $table) {
            if (isset($table->name)) {
                $name = $table->name;
            } else {
                $table = (array) $table;
                $name = reset($table);
            }

            if (in_array($name, ['migrations'], true)) {
                continue;
            }

            $collection = DB::table($name)->get();

            if ($collection->isEmpty()) {
                continue;
            }

            $data = $collection->map(function ($item) {
                unset($item->created_at, $item->updated_at);

                return $item;
            })->toArray();

            dump(sprintf('■■■■■■■■■■■■■■■■■■■ %s %s件 ■■■■■■■■■■■■■■■■■■■', $name, $collection->count()));
            dump($data);
        }

        $this->assertTrue(true);
    }

    /**
     * Dump the database query.
     */
    protected function dumpQuery(): void
    {
        $db = $this->app->make('db');

        $db->enableQueryLog();

        $this->beforeApplicationDestroyed(function () use ($db) {
            dump($db->getQueryLog());
        });
    }
}
