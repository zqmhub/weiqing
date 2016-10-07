<?php
use Testify\Testify;

require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';
load()->func('communication');

if (!empty($_W['ispost'])) {
	print_r(json_encode($_FILES));exit;
	print_r($_POST);exit;
}

$tester = new Testify('测试ihttp_request函数');
$tester->test('测试ihttp_request函数', function() {
	global $_W, $tester;
	$header = <<<EOF
HTTP/1.0 200 OK
Accept-Ranges: bytes
Cache-Control: max-age=604800
Content-Type: text/html
Date: Wed, 21 Sep 2016 02:51:01 GMT
Etag: "359670651"
Expires: Wed, 28 Sep 2016 02:51:01 GMT
Last-Modified: Fri, 09 Aug 2013 23:54:35 GMT
Server: ECS (cpm/F9D5)
Vary: Accept-Encoding
X-Cache: HIT
x-ec-custom-error: 1
Content-Length: 1270
Connection: close

<!doctype html>
<html>
<head>
    <title>Example Domain</title>

    <meta charset="utf-8" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
    body {
        background-color: #f0f0f2;
        margin: 0;
        padding: 0;
        font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        
    }
    div {
        width: 600px;
        margin: 5em auto;
        padding: 50px;
        background-color: #fff;
        border-radius: 1em;
    }
    a:link, a:visited {
        color: #38488f;
        text-decoration: none;
    }
    @media (max-width: 700px) {
        body {
            background-color: #fff;
        }
        div {
            width: auto;
            margin: 0 auto;
            border-radius: 0;
            padding: 1em;
        }
    }
    </style>    
</head>

<body>
<div>
    <h1>Example Domain</h1>
    <p>This domain is established to be used for illustrative examples in documents. You may use this
    domain in examples without prior coordination or asking for permission.</p>
    <p><a href="http://www.iana.org/domains/example">More information...</a></p>
</div>
</body>
</html>
EOF;
	$response = ihttp_response_parse($header);
	$tester->assertEquals($response['code'], '200');
	$tester->assertEquals($response['status'], 'OK');
	
	$header = <<<EOF
HTTP/1.0 300 Multiple Choices
Accept-Ranges: bytes
Cache-Control: max-age=604800
Content-Type: text/html
Date: Wed, 21 Sep 2016 02:51:01 GMT
Etag: "359670651"
Expires: Wed, 28 Sep 2016 02:51:01 GMT
Last-Modified: Fri, 09 Aug 2013 23:54:35 GMT
Server: ECS (cpm/F9D5)
Vary: Accept-Encoding
X-Cache: HIT
x-ec-custom-error: 1
Content-Length: 1270
Connection: close

<!doctype html>
<html>
<head>
    <title>Example Domain</title>

    <meta charset="utf-8" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
    body {
        background-color: #f0f0f2;
        margin: 0;
        padding: 0;
        font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        
    }
    div {
        width: 600px;
        margin: 5em auto;
        padding: 50px;
        background-color: #fff;
        border-radius: 1em;
    }
    a:link, a:visited {
        color: #38488f;
        text-decoration: none;
    }
    @media (max-width: 700px) {
        body {
            background-color: #fff;
        }
        div {
            width: auto;
            margin: 0 auto;
            border-radius: 0;
            padding: 1em;
        }
    }
    </style>    
</head>

<body>
<div>
    <h1>Example Domain</h1>
    <p>This domain is established to be used for illustrative examples in documents. You may use this
    domain in examples without prior coordination or asking for permission.</p>
    <p><a href="http://www.iana.org/domains/example">More information...</a></p>
</div>
</body>
</html>
EOF;
	$response = ihttp_response_parse($header);
	$tester->assertEquals($response['code'], '300');
	$tester->assertEquals($response['status'], 'Multiple Choices');
});

$tester->test('测试php7下ihttp_request函数上传附件', function() {
	global $_W, $tester;
	$response = ihttp_request('http://pro.we7.cc/tester/ihttp_request_function.php', array(
		'name' => '上传图片测试',
		'file' => '@' . IA_ROOT . '/tester/ihttp_request_function.php',
	));
	$json = json_decode($response['content'], true);
	$tester->assertEquals($json['file']['name'], 'ihttp_request_function.php');
});

$tester->run();