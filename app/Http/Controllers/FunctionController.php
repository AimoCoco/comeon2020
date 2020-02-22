<?php

namespace App\Http\Controllers;


use App\Service\FtxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class FunctionController extends Controller
{
    public function index()
    {
        $data = [];
        $ftx1 = new FtxService(1);
        $ftx2 = new FtxService(2);
        $data[1]['low'] = $ftx1->getLowerParam();
        $data[1]['high'] = $ftx1->getHigherParam();
        $data[1]['inFuture'] = $ftx1->isInFuture();
        $data[2]['low'] = $ftx2->getLowerParam();
        $data[2]['high'] = $ftx2->getHigherParam();
        $data[2]['inFuture'] = $ftx2->isInFuture();
        $data['switch'][1] = Redis::get('switch1');
        $data['switch'][2] = Redis::get('switch2');
        return view('ftx', ['data' => $data]);
    }

    public function setParam(Request $request)
    {
        $type = $request->post('type');
        $price = $request->post('price');
        $quantity = $request->post('quantity');
        $time = $request->post('time');

        $setFun = function ($account, $type, $price, $quantity, $time) {
            if ($type == 'low') {
                (new FtxService($account))->setLowerParam($price, $quantity, $time);
            }
            if ($type == 'high') {
                (new FtxService($account))->setHigherParam($price, $quantity, $time);
            }
        };

        switch ($type) {
            case 'low1' : $setFun(1, 'low', $price, $quantity, $time); break;
            case 'high1' : $setFun(1, 'high', $price, $quantity, $time); break;
            case 'low2' : $setFun(2, 'low', $price, $quantity, $time); break;
            case 'high2' : $setFun(2, 'high', $price, $quantity, $time); break;
            default: break;
        }

        return redirect('/');
    }

    public function switch(Request $request)
    {
        $group = $request->get('key');
        $action = $request->get('action');

        Redis::set('switch'.$group, $action);

        return redirect('/');
    }

    public function flush()
    {
        Redis::flush();
    }
}