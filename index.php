<?php
$data = getData();
//start
if(isset($_REQUEST['exp_real'])) {
	$exp = new Experiment($_REQUEST['exp']);
	$ddata = $exp->getData();
	$f = fopen("results.txt", 'a');
	fwrite($f, $ddata . PHP_EOL);
	fclose($f);
	header("Location: index.php");
	exit;
}


function getData() {
	return json_decode(file_get_contents("data.txt") , true);
}

function writeData($data) {
	$jdata = json_encode($data);
	file_put_contents("data.txt", $jdata);
}

function selOpExp() {
	global $data;
	$ret = "";
	foreach($data['experiments'] as $exp) {
		$ret .= "<option>{$exp}</option>";
	}
	return $ret;
}

class Experiment {
	
	var $data = null;
	
	public function __construct($file) {
		$tmp = file($file.".scenario.txt");
		foreach($tmp as $line) {
			$this->data[] = explode(' ', trim($line));
		}
	}
	
	public function outputJS() {
		$ret = "<script type='text/javascript'>";
		$ret .= "var actions = ".json_encode($this->data);
		$ret .= "</script>";
		return $ret;
	}
	
	public function getData() {
		$d = array();
		$post = $_REQUEST;
		unset($post['exp_real']);
		unset($post['exp']);
		unset($post['sbj']);
		$d['subject'] = $_REQUEST['sbj'];
		$d['experiment'] = $_REQUEST['exp'];
		$d['time'] = time();
		foreach($post as $key=>$val) {
			if(strpos($key, "_nr") === false) {
				$d['data'][$key]['original'] = $_REQUEST[$key.'_nr'];
				$d['data'][$key]['answer'] = $_REQUEST[$key];
			}
		}
		return json_encode($d);
	}
}
?>

<html>
<head>
<style>
input, select, textarea {
	width: 100%;
}
</style>
<script src="jquery.js" type="text/javascript"></script>
<script src="VPT.js" type="text/javascript"></script>

<script type='text/javascript'>
    var actions = new Array();
    var idx = -1;
    function start(i) {
        if (i > actions.length) {

        }
        var sleep = actions[i][0];
        var func = action[i][1];
        func();
        if (sleep < 0) {

        } else {
            setTimeout("start(" + (i + 1) + ")", sleep);
        }
    }
    //helpers
    function next(nc) {
        if (!nc) {
            var inputs = document.getElementsByTagName("input");

            for (var i in inputs) {
                //console.log(i);
                //console.log(inputs[i]);
                //console.log(inputs[i].style);
                //console.log('========================================');
                if (inputs[i].style) {
                    inputs[i].style.display = 'none';
                }
            }
            document.getElementById('text').innerHTML = '';
        }

        idx++;
        console.log("NEXT :: " + idx);
        if (actions[idx][0] == "wait") {
            EXP_wait(actions[idx][1]);
        } else if (actions[idx][0] == "background") {
            EXP_background(actions[idx][1]);
        } else if (actions[idx][0] == "displayNr") {
            EXP_displayNr(actions[idx][1], actions[idx][2], actions[idx][3]);
        } else if (actions[idx][0] == "displayGr") {
            EXP_displayGr(actions[idx][1], actions[idx][2], actions[idx][3]);
        } else if (actions[idx][0] == "input") {
            EXP_input(actions[idx][1], actions[idx][2]);
        } else if (actions[idx][0] == "start") {
            EXP_start();
        } else if (actions[idx][0] == "end") {
            EXP_end();
        } else {
            //console.log("NEXT :: ERROR :: "+actions[idx][0]);
        }
    }
    //actions
    function EXP_wait(time) {
        //console.log("WAIT :: "+time);
        setTimeout("next(false)", time * 1000);
    }
    function EXP_background(color) {
        //console.log("BACKGROUND :: "+color);
        document.getElementById('body').style.backgroundColor = color;
        next(false);
    }
    function EXP_displayNr(name, min, max) {
        //console.log("DISPLAY :: "+name+" - "+min+" - "+max);
        var r = parseInt(Math.random() * (max - min));
        r = parseInt(r) + parseInt(min);
        document.getElementById('text').innerHTML = r;
        //console.log("DISPLAY :: "+r);
        var inp = "<input type='hidden' name='" + name + "_nr' value='" + r + "' />";
        var now = document.getElementById('data').innerHTML += inp;
        next(true);
    }
    function EXP_displayGr(name, min, max) {
        

        var canvas = $("#game_table tbody");
        var Game = new VPT(canvas, 4, 4);
        canvas.data('VPT', Game);
        vpt = $('#game_table tbody').data('VPT');
        Game.startGame();
        var inp = "<input type='hidden' name='" + name + "_nr' value='" + r + "' />";
        var now = document.getElementById('data').innerHTML += inp;
        next(true);
    }
    function EXP_input(name, len) {
        //console.log("INPUT :: "+name+" - "+len);
        var inp = "<input type='number' maxlength=" + len + " size=" + len + " id='elem_" + name + "' style='font-size: 80px' />";
        var smt = "<input type='submit' value='Done' onclick='return dummySmt(\"" + name + "\");' />";
        var now = document.getElementById('data').innerHTML;
        document.getElementById('data').innerHTML = now + inp + smt;
    }
    function EXP_start() {
        //console.log("START");
        next(false);
    }
    function EXP_end() {
        //console.log("END");
        document.getElementById('exp_form').submit();
    }
    function dummySmt(name) {
        var inp = "<input type='hidden' name='" + name + "' value='" + document.getElementById('elem_' + name).value + "' />";
        var now = document.getElementById('data').innerHTML += inp;
        next(false);
        return false;
    }
</script>
</head>
<body id='body' style='width: 100%' onload="next(true)">

<center>
	<table id="game_table">
		<tbody>
			<tr><td align="center">WAT Javascript?</td></tr>
		</tbody>
	</table>
	<div id="submitButtonHere"></div>
</center>

<form id='exp_form'>
<?php
if(isset($_REQUEST['exp'])) {
	echo "<input type='hidden' name='exp_real' value='true' /><input type='hidden' name='exp' value='{$_REQUEST['exp']}' /> <input type='hidden' name='sbj' value='{$_REQUEST['sbj']}' />";
	$exp = new Experiment($_REQUEST['exp']);
	echo $exp->outputJS();
} else {
	echo "<script>var actions = new Array();</script>";
}
?>
<br /><br /><br /><br /><br /><br />
<div id='data' style='width: 400px; margin: auto;'>
<?php
if(!isset($_REQUEST['exp'])) {
	echo "<input type='text' name='sbj' style='font-size: 35px' /><br /><select name='exp' style='font-size: 35px'>";
	echo selOpExp();
	echo "</select><br /><input type='submit' value='Start'  style='font-size: 35px/>";
}
?>
</div>
</form>
<br />
<div id='text' unselectable="on" style='width: 400px; margin: auto; text-align: center; font-size: 80px;' >
</div>


<?php

//end
writeData($data);
?>
</body>
</html>