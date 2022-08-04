<?php
class Cliente extends ActiveRecord
{
    protected $table = "cliente";
    protected $idField = "id";
    protected $logTimeStamp = true;


    public static function listRecents(int $days = 10)
    {
        return self::all("created_at >= '" . date('Y-m-d H:m:i', strtotime("-{$days} days")) . "'");
    }

    public static function numTotal()
    {
        return self::count();
    }
}
