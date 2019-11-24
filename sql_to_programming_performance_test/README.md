# SQLで行う処理をプログラムで行う検証
スロークエリに関連して、バッチ処理や一覧表示処理の一部など、

スロークエリを防ぎにくい処理について、

SQLで行う処理と、プログラムで行った時の処理速度とメモリ使用量を比較してみました。

なので、わざとスロークエリになる様にしています（当然、テーブル設計をきちんとしたり、インデックスを貼る等はした方が良いです）。

各種バージョンは、以下の通りです。
- PHP 7.3.11
- Laravel 5.6.39
- MySQL 5.7.23

また、計測して以下の印象を持ちました。

- WHERE句・GROUP BY句・HAVING句（未検証）で、取得する行数が大きく減少する場合は、プログラム側よりSQLで行った方が良い（プログラム側のメモリ節約の為）。
- ORDER BY句（インデックスが貼られていないカラムの場合）・CASE式（未検証）・各種関数（未検証）など、取得する行数は変わらない場合は、プログラム側でソートしたりデータを加工した方が良い（SQL自体が遅くなるのを防ぐ為）。
- Laravelではcursor関数が優秀（ジェネレータが大きいか？）、cursorでも厳しければLaravel内部のPDOを使うと良さそうです。
- それでも厳しい場合は、Laravelならこちら https://github.com/shakahl/laravel-eloquent-mysqli でしょうか？素のmysqliドライバーも、勿論ありですが。

また、以下のサイトも参考にしました。感謝を申し上げます。

Laravel(Eloquent): chunk() vs cursor()
https://qiita.com/ryo511/items/ebcd1c1b2ad5addc5c9d

Eloquent ORMのChunkとCursorをメモリ使用量で比較した
https://blog.zuckey17.org/entry/2018/01/26/195029

SQL だけで100万行 のテストデータを用意する方法
https://qiita.com/chisei/items/c4439adf3d0faedb65ed

【PHP】 プリペアドステートメントの結果を連想配列に変換
http://blog.pionet.co.jp/experience/archives/425
