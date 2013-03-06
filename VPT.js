var MAX_BOARD_SIZE = 6 * 5;
var VIEW_MODE = "VIEW_GRID_MODE";
var INPUT_MODE = "INPUT_GRID_MODE";
var UNDEFINED = "UNDEFINED";
var BLACK_CELL_CLASS = "blackCell";

var COGNITIVE_REST_PERIOD = 15000; //Force the user to memorise the grid for 15 seconds
var INBETWEEN_TRIAL_REST_PERIOD = 15000;
var NUMBER_DISPLAY_PERIOD = 4000; //Show the grid for 4 seconds.
var vpt;

function VPT(Canvas, Width, Height)
{
    console.log("Initialising VPT");
    this._width = Width;
    this._height = Height;
    this._canvas = Canvas;
    this._tableData = null;
    this._userInputData = null;
    this._mode = UNDEFINED;
    this._clickCount = 0;
}
VPT.prototype.reCreateTable = function (width, height)
{
    console.log("Recreating the table");
    if (width * height > MAX_BOARD_SIZE)
    {
        console.log("Board size exceeds maximal dimensions");
        return;
    }
    else
    {
        var html = [];
		var i,j, line;
		i = j = 0;
		line = "";
		
        if (this._mode === VIEW_MODE)
        {
            for (i = 0; i < height; i++)
            {
                line = "<tr>";
                for (j = 0; j < width; j++)
                {
                    line += "<td><div id='cell_" + (i * this._width + j);
                    if (this._tableData[(i * this._width + j)] === 1)
                    {
                        line += "' class='blackCell";
                    }
                    line += "' onclick='cellClickHandler(\"" + (i * this._width + j) + "\");'>" + "</div></td>";
                }
                line += "</tr>";
                html.push(line);
            }
        }
        else if (this._mode === INPUT_MODE)
        {
            for (i = 0; i < height; i++)
            {
                line = "<tr>";
                for (j = 0; j < width; j++)
                {
                    line += "<td><div id='cell_" + (i * this._width + j) + "' onclick='cellClickHandler(\"" + (i * this._width + j) + "\");'>" + "</div></td>";
                }
                line += "</tr>";
                html.push(line);
            }
        }
        else
        {
            alert("I honestly don't know how you managed to execute this piece of code");
        }
        $(this._canvas).html(html.join(""));
    }
}
VPT.prototype.reset = function ()
{
    console.log("Resetting");
    var area = this._width * this._height;
    var half = area / 2;
    if (area > 0 && area <= MAX_BOARD_SIZE)
    {
        this._tableData = new Array(area);
        this._userInputData = new Array(area);
    }
    for (var x = 0; x < (area); x++)
    {
        if (x < half) 
		{
			this._tableData[x] = 1;
		}
        else 
		{
			this._tableData[x] = 0;
		}
		
        this._userInputData[x] = 0;
    }
    this.shuffle(this._tableData);
    this.reCreateTable(this._width, this._height);
    this._clickCount = 0;
}
VPT.prototype.shuffle = function (toShuffle)
{
    console.log("Shuffling");
    var len = toShuffle.length;
    if (len > 0)
    {
        while (--len)
        {
            var randPos = Math.floor(Math.random() * (len + 1));
            var tempA = toShuffle[len];
            var tempB = toShuffle[randPos];
            //Swap
            toShuffle[len] = tempB;
            toShuffle[randPos] = tempA;
        }
        return true;
    }
    else
    {
        return false;
    }
}
VPT.prototype.handleCellClick = function (cellId)
{
    console.log("Handling Cell Click");
    if (cellId < (this._width * this._height))
    {
        if (this._mode === INPUT_MODE)
        {
            var cell = $("#cell_" + cellId);
            cell.toggleClass(BLACK_CELL_CLASS);
            this._userInputData[cellId] = (this._userInputData[cellId] === 0) ? 1 : 0;
        }
        this._clickCount++;
    }
}
VPT.prototype.switchMode = function ()
{
    console.log("Switching Modes");
    if (this._mode === VIEW_MODE)
    {
        this._mode = INPUT_MODE;
		
		this._canvas.hide();
		
		setTimeout(
		function ()
		{
			vpt._canvas.show();
			vpt.reCreateTable(vpt._width, vpt._height);
			$('div#submitButtonHere').after('<button id="submitButton" type="button" onclick="submitSolution()">Submit</button>');
		},
		COGNITIVE_REST_PERIOD);
        
    }
    else if (this._mode === INPUT_MODE)
    {
        this._mode = VIEW_MODE;
        this.reset();
    }
    else
    {
        console.log("Game mode is undefined");
    }
}
VPT.prototype.showPattern = function ()
{
    console.log("Showing Pattern");
    setTimeout(

    function ()
    {
        var VPT = $('#game_table tbody').data('VPT');
        VPT.switchMode();
    },
    NUMBER_DISPLAY_PERIOD);
}
VPT.prototype.startGame = function ()
{
    console.log("Starting Game");
    this._canvas.show();
    this._mode = VIEW_MODE;
    this.reset();
    this.showPattern();
}
VPT.prototype.endGame = function ()
{
    console.log("Ending Game");
    $('#submitButton').remove();
    this._mode = VIEW_MODE;
    this._canvas.hide();
    next();
  //  this.reset();
    
//    setTimeout(
//		function ()
//		{
//            vpt.showPattern();
//		},
//		INBETWEEN_TRIAL_REST_PERIOD);
        

    
}
VPT.prototype.scoreUserInput = function ()
{
    console.log("Scoring User Input");
    var total = this._width * this._height;
    var correct = 0;
    for (var x = 0; x < total; x++)
    {
        if (this._tableData[x] === this._userInputData[x])
		{
			correct++;
		}
    }
    // alert("Congratulations you scored " + correct + " answers out of a possible " + total);
    this.endGame();
}
// //MAIN
// $(function ()
// {
    // console.log("Main");
    // var canvas = $("#game_table tbody");
    // var Game = new VPT(canvas, 4, 4);
    // canvas.data('VPT', Game);
    // vpt = $('#game_table tbody').data('VPT');
    // Game.startGame();
// });

//Handlers
function cellClickHandler(cellId)
{
    vpt.handleCellClick(cellId);
}

function submitSolution()
{
    vpt.scoreUserInput();
}