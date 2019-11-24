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

    private const ID_BETWEEN = [1, 100000];

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
            ($method === 'pdo_chunk_where' && $argument !== 'null'))
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

    private function pdo_fetch_array()
    {
        // 最速（そりゃそうだ）
        // SQL内でwhereで絞り込まないと、落ちる
        // FETCH_CLASSにすると、fetchの途中で処理が終わる
        // 以下全てのケースで同じだが、collect()とtoArray()だと前者の方がメモリを食わなさそう（toArrayは途中終了した）なので、
        // 最初は配列に取得データを入れている
        // 大体、以下前後ぐらい
        // time: 1.053342 memory: 73.160156 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo  = \DB::connection()->getPdo();
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

    private function cursor()
    {
        // メモリがギリギリっぽいが、書くのが楽なのが大きい
        // あと、中身が配列ではなく、唯一のクラス（Eloquent Modelインスタンス）
        // SQL内でwhereで絞り込まないと、落ちる
        // 大体、以下前後ぐらい
        // time: 2.041008 memory: 121.160156 MB
        $result = [];
        foreach (Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->cursor() as $res) {
            $result[] = $res;
        }
        var_dump(count($result));

        return $result;
    }

    //-----------
    // 以下は微妙
    //-----------

    private function pdo_chunk_where($count)
    {
        // 速度も速く、メモリも食わないが、書くのが面倒なのが痛いので微妙か？
        // もっとデータ量が多く、上記２つだと無理な場合は有りか
        // 100000件（1000でも10000でも遅い）ずつ処理
        // FETCH_CLASSにすると、fetchの途中で処理が終わる
        // 大体、以下前後ぐらい
        // time: 1.469098 memory: 73.160156 MB
        $result = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderBy('id')->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();
            $paramTypeList = [\PDO::PARAM_INT];

            $pdo  = \DB::connection()->getPdo();
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
        // 100000件（1000でも10000でも遅い）ずつ処理
        // FETCH_CLASSにすると、fetchの途中で処理が終わる
        // 大体、以下前後ぐらい
        // time: 5.472522 memory: 77.160156 MB
        $result = [];
        $offset = 0;
        while (true) {
            $builder = Item::query()->orderBy('id')->offset($offset)->limit($count);
            $query    = $builder->toSql();
            $bindings = $builder->getBindings();

            $pdo  = \DB::connection()->getPdo();
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

    private function chunk_where($count)
    {
        // 10000件（1000でも100000でも遅い）ずつ処理して、何とかエラーにならずに動作したが、メモリはギリギリっぽい
        // 大体、以下前後ぐらい
        // time: 5.411107 memory: 110.003906 MB
        $result = [];
        Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderBy('id')->chunk(
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
        // SQL内でwhereで絞り込まないと、落ちる
        // 大体、以下前後ぐらい
        // time: 5.389896 memory: 118.007812 MB
        $pkResult = [];
        foreach (Item::query()->select(['id'])->where('item_status', self::ITEM_STATUS_VALUE)->cursor() as $res) {
          $pkResult[] = $res->id;
        }

        $chunk = array_chunk($pkResult, 100);
        $result = [];
        foreach ($chunk as $pkList) {
            foreach (Item::query()->whereIn('id', $pkList)->cursor() as $res) {
                $result[] = $res;
            }
        }
        var_dump(count($result));

        return $result;
    }

    //-----------------
    // 以下は、完全にNG
    //-----------------

    private function chunk($count)
    {
        // 10000件（1000でも100000でも遅い）ずつ処理して、何とかエラーにならずに動作したが、メモリはギリギリっぽい
        // 大体、以下前後ぐらい
        // time: 43.878615 memory: 116.003906 MB
        $result = [];
        Item::query()->orderBy('id')->chunk(
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
        $result = Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->get()->toArray();
        return $result;
    }

    private function get_pk()
    {
        // PKだけ取得でも、上記と変わらない
        // エラーも出ない
        // 何が起きている？
        $result = Item::query()->select(['id'])->where('item_status', self::ITEM_STATUS_VALUE)->get()->toArray();
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

        $pdo  = \DB::connection()->getPdo();
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

        $pdo  = \DB::connection()->getPdo();
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

        $pdo  = \DB::connection()->getPdo();
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
        // SQL内でwhereで絞り込まないと、落ちるので、最初にidでレコードを絞っている
        // 大体、以下前後ぐらい
        // time: 0.091602 memory: 12.000000 MB
        $result = [];
        $builder = Item::query()->select(['item_status'])->whereBetween('id', self::ID_BETWEEN)->groupBy('item_status');
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT, \PDO::PARAM_INT];

        $pdo  = \DB::connection()->getPdo();
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
            $sort[] = $res['item_status'];
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_ASC, $result);

        var_dump(count($result));

        return $result;
    }

    private function pdo_fetch_array_group_by_out()
    {
        // SQL内でwhereで絞り込まないと、落ちるので、最初にidでレコードを絞っている
        // 大体、以下前後ぐらい
        // time: 0.124188 memory: 16.000000 MB
        $result = [];
        $builder = Item::query()->select(['item_status'])->whereBetween('id', self::ID_BETWEEN);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT, \PDO::PARAM_INT];

        $pdo  = \DB::connection()->getPdo();
        $stmt = $pdo->prepare($query);
        foreach ($bindings as $idx => $binding) {
            $stmt->bindValue($idx + 1, $binding, $paramTypeList[$idx]);
        }
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
        // SQL内でwhereで絞り込まないと、落ちるので、最初にidでレコードを絞っている
        // 大体、以下前後ぐらい
        // time: 0.091089 memory: 12.000000 MB
        $result = [];
        $sort = [];
        foreach (Item::query()->select(['item_status'])->whereBetween('id', self::ID_BETWEEN)->groupBy('item_status')->cursor() as $res) {
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
        // SQL内でwhereで絞り込まないと、落ちるので、最初にidでレコードを絞っている
        // 大体、以下前後ぐらい
        // time: 1.624074 memory: 16.000000 MB
        $result = [];
        $itemStatusList = [];
        $sort = [];
        foreach (Item::query()->select(['item_status'])->whereBetween('id', self::ID_BETWEEN)->cursor() as $res) {
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

    //-----------------------------------------------
    // 以下はORDER BY（WHEREで速かったケースだけ実験）
    //-----------------------------------------------

    private function pdo_fetch_array_order_by()
    {
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        // 大体、以下前後ぐらい
        // time: 0.973760 memory: 73.160156 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderByDesc('created_at');
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo  = \DB::connection()->getPdo();
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
        // time: 0.951160 memory: 85.164062 MB
        $result = [];
        $builder = Item::query()->where('item_status', self::ITEM_STATUS_VALUE);
        $query    = $builder->toSql();
        $bindings = $builder->getBindings();
        $paramTypeList = [\PDO::PARAM_INT];

        $pdo  = \DB::connection()->getPdo();
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
        // time: 1.983831 memory: 121.160156 MB
        $result = [];
        foreach (Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->orderByDesc('created_at')->cursor() as $res) {
            $result[] = $res;
        }

        var_dump(count($result));

        return $result;
    }

    private function cursor_order_by_out()
    {
        // NGケース
        // ソート用のデータを作る途中で、処理が終わる
        // SQL内でwhereで絞り込まないと落ちるので絞っている
        $result = [];
        $sort = [];
        foreach (Item::query()->where('item_status', self::ITEM_STATUS_VALUE)->cursor() as $res) {
            $result[] = $res;
            $sort[] = $res->created_at;
            var_dump(count($result));
        }

        // 他のケースとデータ内容を合わせる為にソート
        array_multisort($sort, SORT_DESC, $result);

        var_dump(count($result));

        return $result;
    }
}

