<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class PerformanceTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:test {method} {argument}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private const ITEM_STATUS_VALUE = 7;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            error_reporting(-1);
            // php.iniのデフォルト値にしてみた
            ini_set('memory_limit', '128M');

            $method = $this->argument('method');
            $argument = $this->argument('argument');

            // chunk系のケースは、何件ずつ処理するかを引数で渡す
            if (($method === 'chunk' && $argument !== 'null') ||
                ($method === 'chunk_where' && $argument !== 'null') ||
                ($method === 'pdo_chunk' && $argument !== 'null') ||
                ($method === 'pdo_chunk_where' && $argument !== 'null') ||
                ($method === 'pdo_chunk_group_by' && $argument !== 'null') ||
                ($method === 'pdo_chunk_group_by_out' && $argument !== 'null') ||
                ($method === 'pdo_chunk_order_by' && $argument !== 'null') ||
                ($method === 'pdo_chunk_order_by_out' && $argument !== 'null'))
            {
                $arguments = array((int) $argument);
            }
            else
            {
                $arguments = array();
            }

            $start = microtime(true);

            $ret = call_user_func_array(array($this, $method), $arguments);
            //var_dump($ret);

            $time = microtime(true) - $start;
            $memory = memory_get_peak_usage(true) / 1024 / 1024;

            $this->output->writeln(sprintf('time: %f memory: %f MB', $time, $memory));
        }
        catch (\Error $e) {
            $exception = new \Exception($e->getMessage(), $e->getCode, $e);
            throw $exception;
        }
        catch (\Exception $e) {
            var_dump($e);
        }
    }

    //------------
    // 以下はWHERE
    //------------

    private function pdo_fetch_array_where()
    {
        // 最速（そりゃそうだ）
        // 以下全てのケースで同じだが、collect()とtoArray()だと前者の方がメモリを食わなさそう（toArrayは途中終了した）なので、
        // 最初は配列に取得データを入れている
        // 大体、以下前後ぐらい
        // time: 0.726253 memory: 56.003906 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        foreach ($stmt as $res) {
            $result[] = $res;
        }
        var_dump(count($result));

        return $result;
    }

    private function pdo_fetch_array()
    {
        // 件数が多くても落ちずに速いので、大量データの時はこれが良いか？
        // 大体、以下前後ぐらい
        // time: 1.294815 memory: 56.003906 MB
        $result = [];
        $builder = Item::query();
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        foreach ($stmt as $res) {
            if ($res['item_status'] === self::ITEM_STATUS_VALUE) {
                $result[] = $res;
            }
        }
        var_dump(count($result));

        return $result;
    }

    private function cursor_where()
    {
        // メモリがギリギリっぽいが、書くのが楽なのが大きい
        // あと、中身が配列ではなく、唯一のクラス（Eloquent Modelインスタンス）
        // 大体、以下前後ぐらい
        // time: 1.316269 memory: 106.003906 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            $result[] = $res;
        }
        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk_where($count)
    {
        // 速度も速く、メモリも食わないが、やや書くのが面倒か
        // もっとデータ量が多く、上記のケースだと無理な場合は有り？
        // 100000件（1000でも10000でも遅い）ずつ処理
        // 大体、以下前後ぐらい
        // time: 1.331202 memory: 56.003906 MB
        $result = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderBy('id')->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();
            $paramTypeList = [\PDO::PARAM_INT];

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);
            foreach ($bindings as $idx => $binding) {
                $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
            }
            $stmt->execute();

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                $result[] = $firstRes;
                while (false !== ($res = $stmt->fetch())) {
                    $result[] = $res;
                }
            }
            $offset += $count;
        }
        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk($count)
    {
        // 1000000件（1000でも10000でも100000でも遅い）ずつ処理
        // 大体、以下前後ぐらい
        // time: 1.892999 memory: 56.003906 MB
        $result = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->orderBy('id')->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);
            $stmt->execute($bindings);

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                if ($firstRes['item_status'] === self::ITEM_STATUS_VALUE) {
                    $result[] = $firstRes;
                }
                while (false !== ($res = $stmt->fetch())) {
                    if ($res['item_status'] === self::ITEM_STATUS_VALUE) {
                        $result[] = $res;
                    }
                }
            }
            $offset += $count;
        }
        var_dump(count($result));

        return $result;
    }

    //-----------
    // 以下は微妙
    //-----------

    private function chunk_where($count)
    {
        // 10000件（1000でも100000でも遅い）ずつ処理して、何とかエラーにならずに動作したが、メモリはギリギリっぽい
        // 大体、以下前後ぐらい
        // time: 5.317001 memory: 110.003906 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderBy('id');
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $builder->chunk(
            $count,
            function ($queryResults) use (&$result) {
                foreach ($queryResults as $res) {
                    $result[] = $res;
                }
            }
        );
        var_dump(count($result));

        return $result;
    }

    private function cursor_pk()
    {
        // 大体、以下前後ぐらい
        // time: 4.948090 memory: 120.007812 MB
        $pkResult = [];
        $builder = Item::query()->select(['id'])->where('item_status', self::ITEM_STATUS_VALUE);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            $pkResult[] = $res->id;
        }

        $chunk = array_chunk($pkResult, 100);
        $result = [];
        foreach ($chunk as $pkList) {
            $builder = Item::query()->whereIn('id', $pkList);
            $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $data = $builder->cursor();
            foreach ($data as $res) {
                $result[] = $res;
            }
        }
        var_dump(count($result));

        return $result;
    }

    //-----------------
    // 以下は、完全にNG
    //-----------------

    private function cursor()
    {
        // 大体、以下前後ぐらい
        // time: 17.833993 memory: 106.003906 MB
        $result = [];
        $builder = Item::query();
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            if ($res->item_status === self::ITEM_STATUS_VALUE) {
                $result[] = $res;
            }
        }
        var_dump(count($result));

        return $result;
    }

    private function chunk($count)
    {
        // 10000件（1000でも100000でも遅い）ずつ処理して、何とかエラーにならずに動作したが、メモリはギリギリっぽい
        // 大体、以下前後ぐらい
        // time: 42.927657 memory: 116.003906 MB
        $result = [];
        $builder = Item::query()->orderBy('id');
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $builder->chunk(
            $count,
            function ($queryResults) use (&$result) {
                foreach ($queryResults as $res) {
                    // ここで、where句の処理を行う
                    if ($res->item_status === self::ITEM_STATUS_VALUE) {
                        $result[] = $res;
                    }
                }
            }
        );
        var_dump(count($result));

        return $result;
    }

    private function all()
    {
        // メモリ不足で落ちる（当然）
        $result = Item::all()->toArray();
        return $result;
    }

    private function get()
    {
        // メモリ不足で落ちもしないが、return文まで処理も進まない
        // エラーも出ない
        // 何が起きている？
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $result = $builder->get()->toArray();

        var_dump(count($result));

        return $result;
    }

    private function get_pk()
    {
        // PKだけ取得でも、上記と変わらない
        // エラーも出ない
        // 何が起きている？
        $builder = Item::query()->select(['id'])->where('item_status', self::ITEM_STATUS_VALUE);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $result = $builder->get()->toArray();

        var_dump(count($result));

        return $result;
    }

    private function pdo_fetch()
    {
        // FETCH_CLASSにすると、fetchの途中で処理が終わる
        // エラーも出ない
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_CLASS,
            Item::class,
        );

        foreach ($stmt as $res) {
            $result[] = $res;
            var_dump(count($result));
        }

        return $result;
    }

    private function pdo_fetch_props_late()
    {
        // FETCH_CLASSにすると、fetchの途中で処理が終わる
        // エラーも出ない
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            Item::class,
        );

        foreach ($stmt as $res) {
            $result[] = $res;
            var_dump(count($result));
        }

        return $result;
    }

    private function pdo_fetch_array_to_object()
    {
        // インスタンスの生成が原因か、fetchの途中で処理が終わる
        // エラーも出ない
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        foreach ($stmt as $res) {
            $item = new Item();
            foreach ($res as $key => $val) {
                $item->$key = $val;
            }
            $result[] = $item;
            var_dump(count($result));
        }

        return $result;
    }

    //-----------------------------------------------
    // 以下はGROUP BY（WHEREで速かったケースだけ実験）
    //-----------------------------------------------

    private function pdo_fetch_array_group_by()
    {
        // 大体、以下前後ぐらい
        // time: 0.634974 memory: 12.000000 MB
        $result = [];
        $builder = Item::query()->select(['item_status'])->groupBy('item_status');
        $query    = $builder->toSql();

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);

        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        $sort = [];
        foreach ($stmt as $res) {
            $result[] = $res;
            $sort[] = $res['item_status'];
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function pdo_fetch_array_group_by_out()
    {
        // 大体、以下前後ぐらい
        // time: 0.677400 memory: 12.000000 MB
        $result = [];
        $builder = Item::query()->select(['item_status']);
        $query    = $builder->toSql();

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);

        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        $itemStatusList = [];
        $sort = [];
        foreach ($stmt as $res) {
            if (array_key_exists($res['item_status'], $itemStatusList) === false) {
                $result[] = $res;
                $itemStatusList[$res['item_status']] = true;
                $sort[] = $res['item_status'];
            }
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function cursor_group_by()
    {
        // 大体、以下前後ぐらい
        // time: 0.633010 memory: 12.000000 MB
        $result = [];
        $sort = [];
        $builder = Item::query()->select(['item_status'])->groupBy('item_status');
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            $result[] = $res;
            $sort[] = $res->item_status;
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function cursor_group_by_out()
    {
        // 大体、以下前後ぐらい
        // time: 16.706213 memory: 12.000000 MB
        $result = [];
        $itemStatusList = [];
        $sort = [];
        $builder = Item::query()->select(['item_status']);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            if (array_key_exists($res->item_status, $itemStatusList) === false) {
                $result[] = $res;
                $itemStatusList[$res->item_status] = true;
                $sort[] = $res->item_status;
            }
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk_group_by($count)
    {
        // 10件（総件数）ずつ処理なので、実質１回のSQL実行
        // 大体、以下前後ぐらい
        // time: 1.255493 memory: 12.000000 MB
        $result = [];
        $sort = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->select(['item_status'])->groupBy('item_status')->offset($offset)->limit($count);
            $query    = $builder->toSql();

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);

            $stmt->execute();

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                $result[] = $firstRes;
                $sort[] = $firstRes['item_status'];
                while (false !== ($res = $stmt->fetch())) {
                    $result[] = $res;
                    $sort[] = $res['item_status'];
                }
            }
            $offset += $count;
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk_group_by_out($count)
    {
        // 1000000件（総件数）ずつ処理なので、実質１回のSQL実行
        // 大体、以下前後ぐらい
        // time: 1.110508 memory: 12.000000 MB
        $result = [];
        $itemStatusList = [];
        $sort = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->select(['item_status'])->offset($offset)->limit($count);
            $query    = $builder->toSql();

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);

            $stmt->execute();

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                if (array_key_exists($firstRes['item_status'], $itemStatusList) === false) {
                    $result[] = $firstRes;
                    $itemStatusList[$firstRes['item_status']] = true;
                    $sort[] = $firstRes['item_status'];
                }
                while (false !== ($res = $stmt->fetch())) {
                    if (array_key_exists($res['item_status'], $itemStatusList) === false) {
                        $result[] = $res;
                        $itemStatusList[$res['item_status']] = true;
                        $sort[] = $res['item_status'];
                    }
                }
            }
            $offset += $count;
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    //-----------------------------------------------
    // 以下はORDER BY（WHEREで速かったケースだけ実験）
    //-----------------------------------------------

    private function pdo_fetch_array_order_by()
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 大体、以下前後ぐらい
        // time: 0.638547 memory: 52.003906 MB
        $result = [];
        $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE)->orderByDesc('created_at');
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }

        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        foreach ($stmt as $res) {
            $result[] = $res;
        }
        var_dump(count($result));

        return $result;
    }

    private function pdo_fetch_array_order_by_out()
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 大体、以下前後ぐらい
        // time: 0.556800 memory: 66.007812 MB
        $result = [];
        $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo = $builder->getConnection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }

        $stmt->execute();

        $stmt->setFetchMode(
            \PDO::FETCH_ASSOC
        );

        $sort = [];
        foreach ($stmt as $res) {
            $result[] = $res;
            $sort[] = $res['created_at'];
        }

        array_multisort($sort, SORT_DESC, $result);

        var_dump(count($result));

        return $result;
    }

    private function cursor_order_by()
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 大体、以下前後ぐらい
        // time: 1.657633 memory: 102.003906 MB
        $result = [];
        $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE)->orderByDesc('created_at');
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            $result[] = $res;
        }

        var_dump(count($result));

        return $result;
    }

    private function cursor_order_by_out()
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 大体、以下前後ぐらい
        // time: 5.254312 memory: 116.007812 MB
        $result = [];
        $sort = [];
        $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data = $builder->cursor();
        foreach ($data as $res) {
            $result[] = $res;
            $sort[] = $res->created_at->timestamp;
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_DESC, $result);

        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk_order_by($count)
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 100000件（総件数）ずつ処理なので、実質１回のSQL実行
        // 大体、以下前後ぐらい
        // time: 1.173046 memory: 52.003906 MB
        $result = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE)->orderByDesc('created_at')->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();
            $paramTypeList = [\PDO::PARAM_INT];

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);
            foreach ($bindings as $idx => $binding) {
                $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
            }

            $stmt->execute();

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                $result[] = $firstRes;
                while (false !== ($res = $stmt->fetch())) {
                    $result[] = $res;
                }
            }
            $offset += $count;
        }

        var_dump(count($result));

        return $result;
    }

    private function pdo_chunk_order_by_out($count)
    {
        // 100000件（総件数）ずつ処理なので、実質１回のSQL実行
        // 大体、以下前後ぐらい
        // time: 0.961903 memory: 66.007812 MB
        $result = [];
        $sort = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->select(['created_at'])->where('item_status', self::ITEM_STATUS_VALUE)->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();
            $paramTypeList = [\PDO::PARAM_INT];

            $pdo = $builder->getConnection()->getPdo();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $stmt = $pdo->prepare($query);
            foreach ($bindings as $idx => $binding) {
                $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
            }

            $stmt->execute();

            $stmt->setFetchMode(
                \PDO::FETCH_ASSOC
            );

            // 終了判定をする為、最初の１件は別に扱う
            $firstRes = $stmt->fetch();
            if ($firstRes === false) {
                break;
            } else {
                $result[] = $firstRes;
                $sort[] = $firstRes['created_at'];
                while (false !== ($res = $stmt->fetch())) {
                    $result[] = $res;
                    $sort[] = $res['created_at'];
                }
            }
            $offset += $count;
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function prepared_statement_contains_too_many_placeholders_error()
    {
        // プレースホルダ数が65536以上なので、エラーになるケース
        // 実行時間は、エラーの為、計測できず
        $ids = [];
        for ($i = 1; $i <= 65536; $i++)
        {
            $ids[] = $i;
        }
        $builder = Item::query()->select(['id'])->whereIn('id', $ids);
        try
        {
            $result = $builder->get();
            var_dump($result->count());
        }
        catch (\Exception $e)
        {
            $result = $e->getMessage();
            var_dump($result);
        }

        return $result;
    }

    private function prepared_statement_contains_too_many_placeholders_success_dynamic_placeholder()
    {
        // プレースホルダ数が65536以上だが、動的プレースホルダにするケース
        // time: 56.327581 memory: 112.019531 MB
        $ids = [];
        for ($i = 1; $i <= 65536; $i++)
        {
            $ids[] = $i;
        }
        $builder = Item::query()->select(['id'])->whereIn('id', $ids);
        $builder->getConnection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        try
        {
            $result = $builder->get();
            var_dump($result->count());
            var_dump(implode(',', $result->pluck('id')->toArray()));
        }
        catch (\Exception $e)
        {
            $result = $e->getMessage();
            var_dump($result);
        }

        return $result;
    }

    private function prepared_statement_contains_too_many_placeholders_success_not_placeholder()
    {
        // プレースホルダ数が65536以上だが、プレースホルダにしないケース
        // time: 1.764045 memory: 98.015625 MB
        $ids = [];
        for ($i = 1; $i <= 65536; $i++)
        {
            $ids[] = $i;
        }
        // エラーにする為の値
        //$ids[] = true;

        $is_error = false;
        foreach ($ids as $id)
        {
            // 0以上の正の整数じゃなければエラーにする
            if (is_int($id) === false && is_string($id) === false)
            {
                $is_error = true;
            }
            else
            {
                if (ctype_digit(strval($id)) === false)
                {
                    $is_error = true;
                }
            }
            if ($is_error === true)
            {
                break;
            }
        }

        if ($is_error === false)
        {
            $where = 'id in(' . implode(', ', $ids) . ')';
            $builder = Item::query()->select(['id'])->whereRaw($where);
            try
            {
                $result = $builder->get();
                var_dump($result->count());
                var_dump(implode(',', $result->pluck('id')->toArray()));
            }
            catch (\Exception $e)
            {
                $result = $e->getMessage();
                var_dump($result);
            }
        }
        else
        {
            $result = "0以上の正の整数以外が含まれています。";
            var_dump($result);
        }

        return $result;
    }

    private function prepared_statement_contains_too_many_placeholders_success_array_chunk()
    {
        // プレースホルダ数が65536以上だが、chunkをしてSQLを分割するケース
        // time: 2.901407 memory: 80.015625 MB
        $ids = [];
        for ($i = 1; $i <= 65536; $i++)
        {
            $ids[] = $i;
        }

        $ids_chunks = array_chunk($ids, 1000);
        $result_collection = collect();
        foreach ($ids_chunks as $ids_chunk)
        {
            $builder = Item::query()->select(['id'])->whereIn('id', $ids_chunk);
            // 以下をコメントアウトすると、0.3～0.4秒程速くなった
            //$builder->getConnection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            try
            {
                $result = $builder->get();
                var_dump($result->count());
                $result_collection = $result_collection->merge($result);
            }
            catch (\Exception $e)
            {
                $result = $e->getMessage();
                var_dump($result);
            }
        }

        var_dump($result_collection->count());
        var_dump(implode(',', $result_collection->pluck('id')->toArray()));

        return $result;
    }
}
