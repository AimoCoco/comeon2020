<?php
/**
 * Created by PhpStorm.
 * User: gundam
 * Date: 2020/2/8
 * Time: 2:45 PM
 */

namespace App\Http\Controllers;


use App\PlatformApi\FtxApi;
use App\Service\FtxService;

class IndexController extends Controller
{
    public function index()
    {
//        dd((new FtxService(1))->getIsHaveOption());
        $ftxApi = new FtxApi(
            config('auth.ftx.key1'),
            config('auth.ftx.secret1'),
            config('auth.ftx.future1'));
        dd($ftxApi->getOrdersHistory());
//        dd($ftxApi->orderWithMarket('buy', 0.001));

//        dd($ftxApi->getOptionsAccountInfo());
//        dd($ftxApi->getOptionsPositions()); //result是空数组证明没有期权单了
//        dd($ftxApi->create_market_sell_order('BTC-PERP', 0.001));
//        dd($ftxApi->getAllBalance());
//        dd($ftxApi->getAllBalances());
//        dd($ftxApi->getFutures());
    }
}