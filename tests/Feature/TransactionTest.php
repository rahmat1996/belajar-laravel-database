<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'GADGET', 'Gadget', 'Gadget Category', '2024-01-01 00:00:00'
            ]);
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'FOOD', 'Food', 'Food Category', '2024-01-01 00:00:00'
            ]);
        });

        $results = DB::select("SELECT * FROM categories");
        assertCount(2, $results);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                    'GADGET', 'Gadget', 'Gadget Category', '2024-01-01 00:00:00'
                ]);
                DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                    'GADGET', 'Food', 'Food Category', '2024-01-01 00:00:00'
                ]);
            });
        } catch (QueryException $error) {
        }

        $results = DB::select("SELECT * FROM categories");
        assertCount(0, $results);
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'GADGET', 'Gadget', 'Gadget Category', '2024-01-01 00:00:00'
            ]);
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'FOOD', 'Food', 'Food Category', '2024-01-01 00:00:00'
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $results = DB::select("SELECT * FROM categories");
        assertCount(2, $results);
    }

    public function testManualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'GADGET', 'Gadget', 'Gadget Category', '2024-01-01 00:00:00'
            ]);
            DB::insert("INSERT INTO categories(id,name,description,created_at) VALUES (?,?,?,?)", [
                'GADGET', 'Food', 'Food Category', '2024-01-01 00:00:00'
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $results = DB::select("SELECT * FROM categories");
        assertCount(0, $results);
    }
}
