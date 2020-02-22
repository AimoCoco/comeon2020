<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="10">

    <title>FtxSys</title>

    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
        }

        .full-height {
            height: 80vh;
        }

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
        <div style="border: black solid 1px;">
            <div>
                <table class="table-bordered" align="center" style="width:600px">
                    <tr>
                        <td>第一组账号运行状态</td>
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
                            {{ $data[1]['inFuture'] ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <br>
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
                    <div class="form-group">
                        <label for="time" class="ln">有效期</label>
                        <input type="text" name="time" id="time" value="{{ $data[1]['low'][2] }}" placeholder="">天
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <br>
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
                    <div class="form-group">
                        <label for="time" class="ln">有效期</label>
                        <input type="text" name="time" id="time" placeholder="" value="{{ $data[1]['high'][2] }}">天
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>

        <br>

        <div style="border: black solid 1px;">
            <div>
                <table class="table-bordered" align="center" style="width:600px">
                    <tr>
                        <td>第二组账号运行状态</td>
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
                            {{ $data[2]['inFuture'] ? '是' : '否' }}
                        </td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="table-bordered" style="padding: 1px 10px">
                开仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="low2">
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
                    <div class="form-group">
                        <label for="time" class="ln">有效期</label>
                        <input type="text" name="time" id="time" placeholder="" value="{{ $data[2]['low'][2] }}">天
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
            <br>
            <div class="table-bordered" style="padding: 1px 10px">
                平仓
                <form class="form-inline" action="{{url('function/setparam')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="high2">
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
                    <div class="form-group">
                        <label for="time" class="ln">有效期</label>
                        <input type="text" name="time" id="time" placeholder="" value="{{ $data[2]['high'][2] }}">天
                    </div>
                    <input type="submit" class="btn btn-success ln">
                </form>
            </div>
        </div>
        <div>
            <br>
            <span>
                永续合约: {{ $data['currentPrice'] }} &nbsp;&nbsp;&nbsp;
            </span>
            <a href="/flush" class="btn btn-danger btn-sm" role="button">&nbsp;一键清空&nbsp;</a>
        </div>
    </div>
</div>
</body>
</html>
