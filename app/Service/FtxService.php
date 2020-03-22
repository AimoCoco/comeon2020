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
    public function getIsHaveOption($option = 1)
    {
        if ($option == 2) {
            $optionName = 'auth.ftx.option'.$this->_account . '_2';
        } else {
            $optionName = 'auth.ftx.option'.$this->_account;
        }
        $ftx = new FtxApi(
            config('auth.ftx.key'.$this->_account),
            config('auth.ftx.secret'.$this->_account),
            config($optionName)
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
            // 价格低于
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
                                    // 结束此组
                                    $this->flushThisGroup();
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
                // 价格高于
                if ($this->isInFuture()) {
                    // 已开仓，撤掉保护
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

    public function hedge2()
    {
        // 低于介入保护价格，下开仓条件单(如果失败，改成下市价单)；条件单成交后下平仓条件单(reduceOnly)；无期权了，市价止损，取消所有订单
        if ($btcPrice = $this->getBTCPERPPrice()) {
            // 低于介入保护价格, 下条件单(如果失败，改成下市价单)
            if ($btcPrice < $this->getStartPrice()) {
                $switch = Redis::get('switch'.$this->_account);
                if (!$switch) {
                    return false;
                }

                // 是否已经下了开仓单
                if (!$this->getOpenOrder()) {
                    // 标记下了开仓单
                    $this->setOpenOrder();
                    // 清空对手单
                    $this->_ftx->cancelAllTriggerOrders();

                    if (!$this->_ftx->placeTriggerOrder('sell', $this->getLowerQuantity(), $this->getLowerPrice())) {
                        if (!$this->_ftx->orderWithMarket('sell', $this->getLowerQuantity())) {
                            $this->delOpenOrder();
                            return false;
                        }
                    }
                }

                if ($btcPrice < $this->getLowerPrice()) {
                    // 是否已经下了平仓单
                    if (!$this->getCloseOrder()) {
                        // 标记已经下了平仓单
                        $this->setCloseOrder();
                        if (!$this->_ftx->placeTriggerOrder('buy', $this->getHigherQuantity(), $this->getHigherPrice(), true)) {
                            $this->delCloseOrder();
                            return false;
                        }
                    }

                    if ($this->getIsHaveOption()) {
                        // 有期权
                        return true;
                    } else {
                        // 无期权
                        if ($higherQuantity = $this->getHigherQuantity()) {
                            foreach (range(1, 3) as $value) {
                                if ($this->_ftx->orderWithMarket('buy', $higherQuantity, true)) {
                                    // 结束此组
                                    $this->flushThisGroup1();
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                } else {
                    // 在start和lower之间，如果有了平仓单标记，说明是第二轮了
                    if ($this->getCloseOrder()) {
                        $this->delOpenOrder();
                        $this->delCloseOrder();
                        return true;
                    }
                }

            } elseif ($btcPrice > $this->get2StartPrice()) {
                $switch = Redis::get('switch'.$this->_account.'2');
                if (!$switch) {
                    return false;
                }

                // 是否已经下了开仓单
                if (!$this->get2OpenOrder()) {
                    // 标记下了开仓单
                    $this->set2OpenOrder();
                    // 清空对手单
                    $this->_ftx->cancelAllTriggerOrders();

                    if (!$this->_ftx->placeTriggerOrder('buy', $this->get2HigherQuantity(), $this->get2HigherPrice())) {
                        if (!$this->_ftx->orderWithMarket('buy', $this->get2HigherQuantity())) {
                            $this->del2OpenOrder();
                            return false;
                        }
                    }
                }

                if ($btcPrice > $this->get2HigherPrice()) {
                    // 是否已经下了平仓单
                    if (!$this->get2CloseOrder()) {
                        // 标记已经下了平仓单
                        $this->set2CloseOrder();
                        if (!$this->_ftx->placeTriggerOrder('sell', $this->get2LowerQuantity(), $this->get2LowerPrice(), true)) {
                            $this->del2CloseOrder();
                            return false;
                        }
                    }

                    if ($this->getIsHaveOption(2)) {
                        // 有期权
                        return true;
                    } else {
                        // 无期权
                        if ($higherQuantity = $this->get2LowerQuantity()) {
                            foreach (range(1, 3) as $value) {
                                if ($this->_ftx->orderWithMarket('sell', $higherQuantity, true)) {
                                    // 结束此组
                                    $this->flushThisGroup2();
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                } else {
                    // 在start和higher之间，如果有了平仓单标记，说明是第二轮了
                    if ($this->get2CloseOrder()) {
                        $this->del2OpenOrder();
                        $this->del2CloseOrder();
                        return true;
                    }
                }
            } else {
                return '未触发上下保护介入价格';
            }
        }

        return '获取BTC-PERP价格失败';
    }

    public function run()
    {
//        $switch = Redis::get('switch'.$this->_account);
//        if (!$switch) {
//            return false;
//        }

//        $param = $this->getLowerParam();
//        foreach ($param as $value) {
//            if (empty($value)) {
//                return false;
//            }
//        }

        return $this->hedge2();
    }

    public function flushThisGroup()
    {
        Redis::del($this->_account.'start_lower_price');
        Redis::del($this->_account.'lower_price');
        Redis::del($this->_account.'lower_quantity');
        Redis::del($this->_account.'start_higher_price');
        Redis::del($this->_account.'higher_price');
        Redis::del($this->_account.'higher_quantity');
        Redis::set('switch'.$this->_account, 0);
        return true;
    }

    public function flushThisGroup1()
    {
        Redis::set('switch'.$this->_account, 0);
        Redis::del($this->_account.'start_price');
        Redis::del($this->_account.'lower_price');
        Redis::del($this->_account.'lower_quantity');
        Redis::del($this->_account.'higher_price');
        Redis::del($this->_account.'higher_quantity');
        Redis::del($this->_account.'open_order');
        Redis::del($this->_account.'close_order');
        return true;
    }

    public function flushThisGroup2()
    {
        Redis::set('switch'.$this->_account.'2', 0);
        Redis::del($this->_account.'2start_price');
        Redis::del($this->_account.'2lower_price');
        Redis::del($this->_account.'2lower_quantity');
        Redis::del($this->_account.'2start_higher_price');
        Redis::del($this->_account.'2higher_price');
        Redis::del($this->_account.'2higher_quantity');
        Redis::del($this->_account.'2open_order');
        Redis::del($this->_account.'2close_order');
        return true;
    }

    /****************** start ***************************/
    public function setStartPrice($startPrice)
    {
        Redis::set($this->_account.'start_price', $startPrice);
        return true;
    }

    public function getStartPrice()
    {
        $key = $this->_account . 'start_price';
        return Redis::get($key);
    }

    public function setOpenOrder() {
        Redis::set($this->_account.'open_order', true);
        return true;
    }

    public function delOpenOrder() {
        Redis::del($this->_account.'open_order');
        return true;
    }

    public function getOpenOrder()
    {
        return Redis::get($this->_account.'open_order');
    }

    public function setCloseOrder()
    {
        Redis::set($this->_account.'close_order', true);
        return true;
    }

    public function getCloseOrder()
    {
        return Redis::get($this->_account.'close_order');
    }

    public function delCloseOrder()
    {
        Redis::del($this->_account.'close_order');
        return true;
    }

    /****************** lower ***************************/
    public function setLowerParam($price, $quantity)
    {
        Redis::set($this->_account.'lower_price', $price);
        Redis::set($this->_account.'lower_quantity', $quantity);
        return true;
    }

    public function getLowerParam()
    {
        return Redis::mget([
            $this->_account.'lower_price',
            $this->_account.'lower_quantity',
        ]);
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

    /****************** higher ***************************/
    public function setHigherParam($price, $quantity)
    {
        Redis::set($this->_account.'higher_price', $price);
        Redis::set($this->_account.'higher_quantity', $quantity);
        return true;
    }

    public function getHigherParam()
    {
        return Redis::mget([
            $this->_account.'higher_price',
            $this->_account.'higher_quantity',
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

    /****************** 2start ***************************/
    public function set2StartPrice($startPrice)
    {
        Redis::set($this->_account.'2start_price', $startPrice);
        return true;
    }

    public function get2StartPrice()
    {
        $key = $this->_account . '2start_price';
        return Redis::get($key);
    }

    public function set2OpenOrder() {
        Redis::set($this->_account.'2open_order', true);
        return true;
    }

    public function del2OpenOrder() {
        Redis::del($this->_account.'2open_order');
        return true;
    }

    public function get2OpenOrder()
    {
        return Redis::get($this->_account.'2open_order');
    }

    public function set2CloseOrder()
    {
        Redis::set($this->_account.'2close_order', true);
        return true;
    }

    public function get2CloseOrder()
    {
        return Redis::get($this->_account.'2close_order');
    }

    public function del2CloseOrder()
    {
        Redis::del($this->_account.'2close_order');
        return true;
    }

    /****************** lower ***************************/
    public function set2LowerParam($price, $quantity)
    {
        Redis::set($this->_account.'2lower_price', $price);
        Redis::set($this->_account.'2lower_quantity', $quantity);
        return true;
    }

    public function get2LowerParam()
    {
        return Redis::mget([
            $this->_account.'2lower_price',
            $this->_account.'2lower_quantity',
        ]);
    }

    public function get2LowerPrice()
    {
        $key = $this->_account . '2lower_price';
        return Redis::get($key);
    }

    public function get2LowerQuantity()
    {
        $key = $this->_account . '2lower_quantity';
        return Redis::get($key);
    }

    /****************** higher ***************************/
    public function set2HigherParam($price, $quantity)
    {
        Redis::set($this->_account.'2higher_price', $price);
        Redis::set($this->_account.'2higher_quantity', $quantity);
        return true;
    }

    public function get2HigherParam()
    {
        return Redis::mget([
            $this->_account.'2higher_price',
            $this->_account.'2higher_quantity',
        ]);
    }

    public function get2HigherPrice()
    {
        $key = $this->_account . '2higher_price';
        return Redis::get($key);
    }

    public function get2HigherQuantity()
    {
        $key = $this->_account . '2higher_quantity';
        return Redis::get($key);
    }

    /*************** future *******************/
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