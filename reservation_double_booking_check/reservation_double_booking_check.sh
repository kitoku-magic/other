#!/bin/sh

if [ $# -ne 2 ]; then
  echo "指定された引数は$#個です。" 1>&2
  echo "実行するには2個の引数が必要です。" 1>&2
  exit 1
fi

if [ $2 == 'insert' ]; then
  php reservation_double_booking_check.php $1 1 2017-10-09 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 18:30:00 20:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 17:30:00 19:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 19:30:00 21:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-10 21:30:00 23:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-10 22:00:00 23:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-10 21:00:00 22:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 17:00:00 18:30:00 &
elif [ $2 == 'update' ]; then
  php reservation_double_booking_check.php $1 1 2017-10-09 14:30:00 16:00:00 1 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 21:00:00 22:30:00 1 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 16:00:00 17:30:00 1 2017-10-09 19:30:00 21:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-10 19:30:00 21:00:00 1 2017-10-10 21:00:00 22:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 19:30:00 21:00:00 2 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 14:30:00 16:00:00 2 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 18:30:00 20:00:00 2 2017-10-10 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 17:30:00 19:00:00 1 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 20:30:00 22:00:00 1 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 1 2017-10-09 15:00:00 16:30:00 1 2017-10-09 19:30:00 21:00:00 &
  php reservation_double_booking_check.php $1 1 2017-10-10 21:30:00 23:00:00 1 2017-10-10 21:00:00 22:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 15:30:00 17:00:00 2 2017-10-09 16:00:00 17:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-09 19:00:00 20:30:00 2 2017-10-09 18:00:00 19:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 18:30:00 20:00:00 2 2017-10-10 17:00:00 18:30:00 &
  php reservation_double_booking_check.php $1 2 2017-10-10 16:00:00 17:30:00 2 2017-10-10 17:00:00 18:30:00 &
else
  echo "不正なパラメータ:${2}"
fi

exit
