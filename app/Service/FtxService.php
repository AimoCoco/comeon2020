<?php
/**
 * Created by PhpStorm.
 * User: gundam
 * Date: 2020/2/8
 * Time: 4:03 PM
 */

namespace App\Service;


use App\PlatformApi\FtxApi;
use Illuminate\Support\Facades\Redis;

class FtxService
{
    private $_account = '';
    private $_ftx = null;

    public function __construct($account)
    {
        $this->_account = $account;
        $this->_ftx = new FtxApi(
            config('auth.ftx.key'.$account),
            config('auth.ftx.secret'.$account),
            config('auth.ftx.future'.$account)
        );
    }

    /**
     * 获取BTC永续合约最新成交价
     *
     * @return mixed
     */
    public function getBTCPERPPrice()
    {
        $price = $this->_ftx->getOneFuture('BTC-PERP');
        if (isset($price['last'])) {
            return $price['last'];
        }
        return false;
    }

    /**
     * 是否持有期权，3次都没才算没
     *
     * @return bool
     */
    public function getIsHaveOption()
    {
        $ftx = new FtxApi(
            config('auth.ftx.key'.$this->_account),
            config('auth.ftx.secret'.$this->_account),
            config('auth.ftx.option'.$this->_account)
        );
        $count = 0;
        foreach (range(1, 3) as $value) {
            $option = $ftx->getOptionsPositions();
            if ($option !== false && empty($option)) {
                $count++;
            }
        }
        if ($count === 3) {
            return false;
        }
        return true;
    }

    /**
     * 对冲
     *
     * @return bool|string
     */
    public function hedge()
    {
        if ($btcPrice = $this->getBTCPERPPrice()) {
            if ($btcPrice < $this->getLowerPrice()) {
                // 已开仓
                if ($this->isInFuture()) {
                    if ($this->getIsHaveOption()) {
                        // 有期权
                        return true;
                    } else {
                        // 无期权
                        if ($higherQuantity = $this->getHigherQuantity()) {
                            foreach (range(1,3) as $value) {
                                if ($this->_ftx->orderWithMarket('buy', $higherQuantity)) {
                                    $this->delFuture();
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                } else {
                    // 未开仓
                    if ($lowerQuantity = $this->getLowerQuantity()) {
                        foreach (range(1,3) as $value) {
                            if ($this->_ftx->orderWithMarket('sell', $lowerQuantity)) {
                                $this->markInFuture();
                                return true;
                            }
                        }
                    }
                }
            } else {
                if ($this->isInFuture()) {
                    // 已开仓
                    if ($higherQuantity = $this->getHigherQuantity()) {
                        foreach (range(1,3) as $value) {
                            if ($this->_ftx->orderWithMarket('buy', $higherQuantity)) {
                                $this->delFuture();
                                return true;
                            }
                        }
                    }
                    return false;
                } else {
                    // 未开仓
                    return true;
                }
            }
        }

        return '获取BTC-PERP价格失败';
    }

    public function run()
    {
        $switch = Redis::get('switch'.$this->_account);
        if (!$switch) {
            return false;
        }

        if (time() > $this->getLowerEndTime()) {
            return false;
        }

        $param = $this->getLowerParam();
        foreach ($param as $value) {
            if (empty($value)) {
                return false;
            }
        }

        return $this->hedge();
    }

    public function getLowerPrice()
    {
        $key = $this->_account . 'lower_price';
        return Redis::get($key);
    }

    public function getLowerQuantity()
    {
        $key = $this->_account . 'lower_quantity';
        return Redis::get($key);
    }

    public function getLowerTime()
    {
        $key = $this->_account . 'lower_time';
        return Redis::get($key);
    }

    public function getLowerEndTime()
    {
        return Redis::get($this->_account.'lower_end_time');
    }

    public function setLowerParam($price, $quantity, $time)
    {
        Redis::set($this->_account.'lower_price', $price);
        Redis::set($this->_account.'lower_quantity', $quantity);
        Redis::set($this->_account.'lower_time', $time);
        Redis::set($this->_account.'higher_time', $time);
        $end = time() + $time * 86400;
        Redis::set($this->_account.'lower_end_time', $end);
        return true;
    }

    public function getLowerParam()
    {
        return Redis::mget([
            $this->_account.'lower_price',
            $this->_account.'lower_quantity',
            $this->_account.'lower_time'
        ]);
    }

    public function getHigherPrice()
    {
        $key = $this->_account . 'higher_price';
        return Redis::get($key);
    }

    public function getHigherQuantity()
    {
        $key = $this->_account . 'higher_quantity';
        return Redis::get($key);
    }

    public function getHigherTime()
    {
        $key = $this->_account . 'higher_time';
        return Redis::get($key);
    }

    public function setHigherParam($price, $quantity, $time)
    {
        Redis::set($this->_account.'higher_price', $price);
        Redis::set($this->_account.'higher_quantity', $quantity);
        Redis::set($this->_account.'lower_time', $time);
        Redis::set($this->_account.'higher_time', $time);
        $end = time() + $time * 86400;
        Redis::set($this->_account.'lower_end_time', $end);
        return true;
    }

    public function getHigherParam()
    {
        return Redis::mget([
            $this->_account.'higher_price',
            $this->_account.'higher_quantity',
            $this->_account.'higher_time'
        ]);
    }

    public function isInFuture()
    {
        $key = $this->_account . 'future';
        return Redis::get($key);
    }

    public function markInFuture()
    {
        $key = $this->_account . 'future';
        return Redis::set($key, 1);
    }

    public function delFuture()
    {
        $key = $this->_account . 'future';
        return Redis::del($key);
    }
}