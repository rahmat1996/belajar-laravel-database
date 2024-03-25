<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("delete from products");
        DB::delete("delete from categories");
        DB::delete("delete from counters");
    }

    public function testInsert()
    {
        DB::table("categories")->insert([
            "id" => "GADGET",
            "name" => "Gadget",
            "description" => "Gadget Category",
            "created_at" => "2024-01-01 00:00:00"
        ]);

        DB::table("categories")->insert([
            "id" => "FOOD",
            "name" => "Food",
            "description" => "Food Category",
            "created_at" => "2024-01-01 00:00:00"
        ]);

        $result = DB::select("select count(id) as total from categories");
        assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();
        $collection = DB::table("categories")->select(['id', 'name'])->get();
        $this->assertNotNull($collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertCategories()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->insertCategories();
        $collection = DB::table("categories")->where(function (Builder $builder) {
            $builder->where("id", "=", "SMARTPHONE");
            $builder->orWhere("id", "=", "LAPTOP");
        })->get();

        $this->assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();
        $collection = DB::table("categories")
            ->whereBetween("created_at", ["2024-02-16 10:10:10", "2024-04-16 10:10:10"])
            ->get();

        $this->assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();
        $collection = DB::table("categories")->whereIn("id", ["SMARTPHONE", "LAPTOP"])->get();
        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();
        $collection = DB::table("categories")->whereNull("description")->get();
        $this->assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();
        $collection = DB::table("categories")->whereDate("created_at", "=", "2024-03-16")->get();
        $this->assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();
        DB::table("categories")->where("id", "=", "SMARTPHONE")->update(["name" => "Handphone"]);
        $collection = DB::table("categories")->where("name", "=", "Handphone")->get();
        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpsert()
    {
        $this->insertCategories();
        DB::table("categories")->updateOrInsert(
            ["id" => "VOUCHER"],
            [
                "name" => "Voucher",
                "created_at" => "2024-03-16 10:10:10"
            ]
        );
        $collection = DB::table("categories")->where("id", "=", "VOUCHER")->get();
        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        $this->seed(CounterSeeder::class);
        DB::table("counters")->where("id", "=", "sample")->increment('counter', 1);
        $collection = DB::table("counters")->where("id", "=", "sample")->get();
        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insertCategories();
        DB::table("categories")->where("id", "=", "SMARTPHONE")->delete();
        $collection = DB::table("categories")->where("id", "=", "SMARTPHONE")->get();
        $this->assertCount(0, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertProducts()
    {
        $this->insertCategories();
        DB::table('products')->insert([
            "id" => "1",
            "name" => "Iphone 14 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
        DB::table('products')->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S21 Ultra",
            "category_id" => "SMARTPHONE",
            "price" => 18000000
        ]);
    }

    public function testJoin()
    {
        $this->insertProducts();
        $collection = DB::table("products")
            ->join("categories", "products.category_id", "=", "categories.id")
            ->select("products.id", "products.name", "categories.name as category_name", "products.price")
            ->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertProducts();
        $collection = DB::table("products")
            ->orderBy("price", "desc")
            ->orderBy("name", "asc")
            ->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insertCategories();
        $collection = DB::table("categories")
            ->skip(2)
            ->take(2)
            ->get();
        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table("categories")->insert([
                "id" => "CATEGORY-{$i}",
                "name" => "Category {$i}",
                "created_at" => "2024-03-17 10:10:10"
            ]);
        }
    }

    public function testChunk()
    {
        $this->insertManyCategories();
        DB::table("categories")
            ->orderBy("id", "asc")
            ->chunk(10, function ($categories) {
                $this->assertNotNull($categories);
                Log::info("Start Chunk");
                $categories->each(function ($item) {
                    Log::info(json_encode($item));
                });
                Log::info("End Chunk");
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();
        $collection = DB::table("categories")->orderBy("id")->lazy(10)->take(3);
        $this->assertNotNull($collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testCursor()
    {
        $this->insertManyCategories();
        $collection = DB::table("categories")->orderBy("id")->cursor();
        $this->assertNotNull($collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testAggregate()
    {
        $this->insertProducts();

        // Count
        $result = DB::table("products")->count();
        $this->assertEquals(2, $result);

        // Min
        $result = DB::table("products")->min("price");
        $this->assertEquals(18000000, $result);

        // Max
        $result = DB::table("products")->max("price");
        $this->assertEquals(20000000, $result);

        // Average
        $result = DB::table("products")->avg("price");
        $this->assertEquals(19000000, $result);

        // Sum
        $result = DB::table("products")->sum("price");
        $this->assertEquals(38000000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProducts();
        $collection = DB::table("products")
            ->select(
                DB::raw("count(id) as total_product"),
                DB::raw("min(price) as min_price"),
                DB::raw("max(price) as max_price")
            )->get();

        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(18000000, $collection[0]->min_price);
        $this->assertEquals(20000000, $collection[0]->max_price);
    }

    public function insertProductFood()
    {
        DB::table('products')->insert([
            "id" => "3",
            "name" => "Bakso",
            "category_id" => "FOOD",
            "price" => 12000
        ]);
        DB::table('products')->insert([
            "id" => "4",
            "name" => "Soto",
            "category_id" => "FOOD",
            "price" => 12000
        ]);
    }

    public function testGroupBy()
    {
        $this->insertProducts();
        $this->insertProductFood();
        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->get();

        $this->assertCount(2, $collection);
        $this->assertEquals("SMARTPHONE", $collection[0]->category_id);
        $this->assertEquals("FOOD", $collection[1]->category_id);
        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(2, $collection[1]->total_product);
    }

    public function testGroupByHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();
        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->having(DB::raw("count(*)", ">", 2))
            ->get();

        $this->assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertProducts();
        DB::transaction(function () {
            $collection = DB::table("products")
                ->where("id", "=", "1")
                ->lockForUpdate()
                ->get();

            $this->assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insertCategories();
        $paginate = DB::table("categories")->paginate(perPage: 2, page: 1);
        $this->assertEquals(1, $paginate->currentPage());
        $this->assertEquals(2, $paginate->perPage());
        $this->assertEquals(2, $paginate->lastPage());
        $this->assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        $this->assertCount(2, $collection);
        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }
    }

    public function testIterateAllPagination()
    {
        $this->insertCategories();
        $page = 1;
        while (true) {
            $paginate = DB::table("categories")->paginate(perPage: 2, page: $page);
            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;
                $collection = $paginate->items();
                $this->assertCount(2, $collection);
                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->insertCategories();
        $cursor = "id";
        while (true) {
            $paginate = DB::table("categories")->orderBy("id")->cursorPaginate(perPage: 2, cursor: $cursor);
            foreach ($paginate->items() as $item) {
                $this->assertNotNull($item);
                Log::info(json_encode($item));
            }
            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
