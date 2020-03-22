<?php

namespace App\Http\Controllers;


use App\PlatformApi\FtxApi;
use App\Service\FtxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class FunctionController extends Controller
{
    public function index()
    {
        $data = [];

        // A组 SELLPUT 和 SELLCALL
        $ftx1 = new FtxService(1);
        $data[1]['start'] = $ftx1->getStartPrice();
        $data[1]['low'] = $ftx1->getLowerParam();
        $data[1]['high'] = $ftx1->getHigherParam();
        $data[1]['2start'] = $ftx1->get2StartPrice();
        $data[1]['2low'] = $ftx1->get2LowerParam();
        $data[1]['2high'] = $ftx1->get2HigherParam();

        // B组 SELLPUT 和 SELLCALL
        $ftx2 = new FtxService(2);
        $data[2]['start'] = $ftx2->getStartPrice();
        $data[2]['low'] = $ftx2->getLowerParam();
        $data[2]['high'] = $ftx2->getHigherParam();
        $data[2]['2start'] = $ftx2->get2StartPrice();
        $data[2]['2low'] = $ftx2->get2LowerParam();
        $data[2]['2high'] = $ftx2->get2HigherParam();

        $data['switch'][1] = Redis::get('switch1');
        $data['switch'][12] = Redis::get('switch12');
        $data['switch'][2] = Redis::get('switch2');
        $data['switch'][22] = Redis::get('switch22');

        $data['currentPrice'] = $ftx1->getBTCPERPPrice();

        $data['1lowSub'] = config('auth.ftx.future1');
        $data['1highSub'] = config('auth.ftx.option1');
        $data['1highSub2'] = config('auth.ftx.option1_2');

        $data['2lowSub'] = config('auth.ftx.future2');
        $data['2highSub'] = config('auth.ftx.option2');
        $data['2highSub2'] = config('auth.ftx.option2_2');

        return view('ftx', ['data' => $data]);
    }

    public function setParam(Request $request)
    {
        $type = $request->post('type');
        $price = $request->post('price');
        $quantity = $request->post('quantity');

        $setFun = function ($account, $type, $price, $quantity) {
            if ($type == 'start') {
                (new FtxService($account))->setStartPrice($price);
            }
            if ($type == 'start2') {
                (new FtxService($account))->set2StartPrice($price);
            }
            if ($type == 'low') {
                (new FtxService($account))->setLowerParam($price, $quantity);
            }
            if ($type == 'high') {
                (new FtxService($account))->setHigherParam($price, $quantity);
            }
            if ($type == 'low2') {
                (new FtxService($account))->set2LowerParam($price, $quantity);
            }
            if ($type == 'high2') {
                (new FtxService($account))->set2HigherParam($price, $quantity);
            }
        };

        switch ($type) {
            case '1start' : $setFun(1, 'start', $price, $quantity); break;
            case 'low1' : $setFun(1, 'low', $price, $quantity); break;
            case 'high1' : $setFun(1, 'high', $price, $quantity); break;
            case '1start_2' : $setFun(1, 'start2', $price, $quantity); break;
            case 'low1_2' : $setFun(1, 'low2', $price, $quantity); break;
            case 'high1_2' : $setFun(1, 'high2', $price, $quantity); break;

            case '2start' : $setFun(2, 'start', $price, $quantity); break;
            case '2low1' : $setFun(2, 'low', $price, $quantity); break;
            case '2high1' : $setFun(2, 'high', $price, $quantity); break;
            case '21start_2' : $setFun(2, 'start2', $price, $quantity); break;
            case '2low1_2' : $setFun(2, 'low2', $price, $quantity); break;
            case '2high1_2' : $setFun(2, 'high2', $price, $quantity); break;
            default: break;
        }

        return redirect('/5e1ac823555215b0');
    }

    public function switch(Request $request)
    {
        $group = $request->get('key');
        $action = $request->get('action');

        Redis::set('switch'.$group, $action);

        return redirect('/5e1ac823555215b0');
    }

    public function flush(Request $request)
    {
        $group = $request->post('g');
        switch($group) {
            case 1: (new FtxService(1))->flushThisGroup1(); break;
            case 12: (new FtxService(1))->flushThisGroup2(); break;
            case 2: (new FtxService(2))->flushThisGroup1(); break;
            case 22: (new FtxService(2))->flushThisGroup2(); break;
        }
        return redirect('/5e1ac823555215b0');
    }
}