<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="20">

    <title>FtxSys</title>

    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
        }

        /*.full-height {*/
            /*height: 80vh;*/
        /*}*/

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .content {
            text-align: center;
        }

        .rn {
            margin-right: 10px;
        }

        .ln {
            margin-left: 30px;
        }

    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div style="border: black solid 1px; float: left">
            <div>
                <table class="table-bordered" align="center" style="">
                    <tr>
                        <td>A组 SELLPUT账户组</td>
                        <td>
                            OPTION设立的账号名称：{{ $data['1highSub'] }} <br>
                            OPTION对冲保护的账号名：{{ $data['1lowSub'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>账号运行状态</td>
                        <td>
                            <?php if($data['switch'][1]) { ?>
                                run &nbsp;<a href="/switch?key=1&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>
                                <?php } else { ?>
                                stop &nbsp;<a href="/switch?key=1&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>是否进入强平保护</td>
                        <td>
                            {{ $data['switch'][1] && $data['currentPrice'] < $data[1]['low'][0] ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="1start">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        SELLPUT保护介入价格
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['start'] }}">USD
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                开仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="low1">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        低于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['low'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        卖出
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[1]['low'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                平仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="high1">
                    <div class="form-group">
                        <label for="higher" class="rn"></label>
                        高于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['high'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        买入
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[1]['high'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>

        <div style="border: black solid 1px; float: right">
            <div>
                <table class="table-bordered" align="center" style="width:600px">
                    <tr>
                        <td>A组 SELLCALL账户组</td>
                        <td>
                            OPTION设立的账号名称：{{ $data['1highSub2'] }} <br>
                            OPTION对冲保护的账号名：{{ $data['1lowSub'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>账号运行状态</td>
                        <td>
                            <?php if($data['switch'][12]) { ?>
                            run &nbsp;<a href="/switch?key=12&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>
                            <?php } else { ?>
                            stop &nbsp;<a href="/switch?key=12&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>是否进入强平保护</td>
                        <td>
                            {{ $data['switch'][2] && $data['currentPrice'] > $data[1]['2high'][0]  ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="1start_2">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        SELLCALL保护介入价格
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['2start'] }}">USD
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                开仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="high1_2">
                    <div class="form-group">
                        <label for="higher" class="rn"></label>
                        高于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['2high'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        买入
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[1]['2high'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                平仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="low1_2">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        低于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[1]['2low'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        卖出
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[1]['2low'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>

        <div>
            <br>
            <a href="/flush?g=1" class="btn btn-danger btn-sm" role="button">&nbsp;清空A组左&nbsp;</a>
            <span>
               &nbsp;&nbsp;&nbsp; 永续合约: {{ $data['currentPrice'] }} &nbsp;&nbsp;&nbsp;
            </span>
            <a href="/flush?g=12" class="btn btn-danger btn-sm" role="button">&nbsp;清空A组右&nbsp;</a>
        </div>

        <div style="border: black solid 1px; float: left">
            <div>
                <table class="table-bordered" align="center" style="">
                    <tr>
                        <td>B组 SELLPUT账户组</td>
                        <td>
                            OPTION设立的账号名称：{{ $data['2highSub'] }} <br>
                            OPTION对冲保护的账号名：{{ $data['2lowSub'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>账号运行状态</td>
                        <td>
                            <?php if($data['switch'][2]) { ?>
                            run &nbsp;<a href="/switch?key=2&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>
                            <?php } else { ?>
                            stop &nbsp;<a href="/switch?key=2&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>是否进入强平保护</td>
                        <td>
                            {{ $data['switch'][2] && $data['currentPrice'] < $data[2]['low'][0] ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2start">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        SELLPUT保护介入价格
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['start'] }}">USD
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                开仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2low1">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        低于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['low'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        卖出
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[2]['low'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                平仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2high1">
                    <div class="form-group">
                        <label for="higher" class="rn"></label>
                        高于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['high'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        买入
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[2]['high'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>

        <div style="border: black solid 1px; float: right">
            <div>
                <table class="table-bordered" align="center" style="width:600px">
                    <tr>
                        <td>B组 SELLCALL账户组</td>
                        <td>
                            OPTION设立的账号名称：{{ $data['2highSub2'] }} <br>
                            OPTION对冲保护的账号名：{{ $data['2lowSub'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>账号运行状态</td>
                        <td>
                            <?php if($data['switch'][22]) { ?>
                            run &nbsp;<a href="/switch?key=22&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>
                            <?php } else { ?>
                            stop &nbsp;<a href="/switch?key=22&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>是否进入强平保护</td>
                        <td>
                            {{ $data['switch'][22] && $data['currentPrice'] > $data[2]['2high'][0]  ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2start_2">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        SELLCALL保护介入价格
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['2start'] }}">USD
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                开仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2high1_2">
                    <div class="form-group">
                        <label for="higher" class="rn"></label>
                        高于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['2high'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        买入
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[2]['2high'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <div class="table-bordered" style="padding: 1px 10px">
                平仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data" >
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="2low1_2">
                    <div class="form-group">
                        <label for="lower" class="rn"></label>
                        低于
                    </div>
                    <div class="form-group">
                        <label for="price" class="rn"></label>
                        <input type="text" name="price" id="price" value="{{ $data[2]['2low'][0] }}">USD
                    </div>
                    <div class="form-group">
                        <label for="do" class="ln"></label>
                        卖出
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="rn"></label>
                        <input type="text" name="quantity" id="quantity" value="{{ $data[2]['2low'][1] }}">个BTC
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>

        <div>
            <br>
            <a href="/flush?g=2" class="btn btn-danger btn-sm" role="button">&nbsp;清空B组左&nbsp;</a>
            <span>
               &nbsp;&nbsp;&nbsp; 永续合约: {{ $data['currentPrice'] }} &nbsp;&nbsp;&nbsp;
            </span>
            <a href="/flush?g=22" class="btn btn-danger btn-sm" role="button">&nbsp;清空B组右&nbsp;</a>
        </div>

    </div>
</div>
</body>
</html>
