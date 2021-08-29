class Numbers{
    constructor(field,numsList,className) {
        this.field = field;
        this.numsList = numsList;
        this._canvas = field.elem.getElementsByClassName(className)[0];
        this._ctx = this._canvas.getContext("2d");
        this.resizeCanvas();
    }

    getPixelsCount(cellsCount){
        return (cellsCount+2)*this._field.step;
    }
    
    set field(value){
        this._field = value;
        this._numbersMenu = value.numbersMenu;
    }
    
    set numsList(value){
        if (value==null){
            this._numsList = [];
        } else {
            this._numsList = value;
        }
    }
    
    get numbersMenu(){
        return this._numbersMenu;
    }

    get numsList(){
        return this._numsList;
    }

    get lineNumsMaxCount(){
        if (this._lineNumsMaxCount!=null)
                return this._lineNumsMaxCount;

        this._lineNumsMaxCount = 0;
        for (let i=0; i<this.numsList.length; i++)
                this._lineNumsMaxCount = Math.max(this._lineNumsMaxCount,this.numsList[i].length);
        return this._lineNumsMaxCount;
    }
	
    get lineCount(){
        return this.numsList.length;
    }

    getNumberLength(lineInd,numberInd) {
        if (this.numsList[lineInd]===undefined || this.numsList[lineInd][numberInd]===undefined)
            return undefined;

        return this.numsList[lineInd][numberInd];
    }

    changeNumsList(lineInd,numberInd,numberLength){
        if (Number.isInteger(numberLength) && numberLength>0){
            if (lineInd<0){
                this.numsList.unshift([numberLength]);
            } else if (lineInd>this.numsList.length-1){
                this.numsList[lineInd] = [numberLength];
            } else if (numberInd<0){
                this.numsList[lineInd].unshift(+numberLength);
            } else {
                this.numsList[lineInd][numberInd] = +numberLength;
            }
        } else {
            if (lineInd>=0 && lineInd<this.numsList.length){
                if (this.numsList[lineInd][numberInd]!==undefined){
                    this.numsList[lineInd].splice(numberInd,1);
                }
                if (this.numsList[lineInd].length==0){
                    this.numsList.splice(lineInd,1);
                }
            }
        }

    }
	
    resizeCanvas(){
        this._lineNumsMaxCount = null;
        this._canvas.width = this.canvasWidth;
        this._canvas.height = this.canvasHeight;
    }

    redraw(){
        this._ctx.clearRect(0,0,this._canvas.width,this._canvas.height);
        this.resizeCanvas();
        this.draw();
    }

    draw(){
        this.drawGrid();

        for(let y=0; y<this.lineCount; y++)
        for(let x=0; x<this.lineNumsMaxCount; x++)
        {
            let x1 = (this.lineNumsMaxCount - this.numsList[y].length) + x;
            if (this.numsList[y][x]!=undefined)
            {
                let fontSize = this._field.step*12/16;
                this._ctx.font = "bold "+fontSize+"px Arial";
                this._ctx.fillText(this.numsList[y][x], this.getNumCellX(x1,y), this.getNumCellY(x1,y));
            }
        }
    }

    get sum(){
        let sum = 0;
        for(let y=0; y<this.lineCount; y++)
        for(let x=0; x<this.numsList[y].length; x++)
        {
            sum+=this.numsList[y][x];
        }

        return sum;
    }
    

    onClick(e) {
        let elem = e.target;
        let top = 0;
        let left = 0;
        while(elem) {
            top = top + parseFloat(elem.offsetTop);
            left = left + parseFloat(elem.offsetLeft);
            elem = elem.offsetParent;
        }

        let x = e.pageX - left;
        let y = e.pageY - top;
        
        let currCellX = Math.floor(x/this._field.step);
        let currCellY = Math.floor(y/this._field.step);
        
        this.numbersMenu.currNumsName = e.target.className;
        this.numbersMenu.currLineInd = this.getLineInd(currCellX,currCellY);
        this.numbersMenu.currNumberInd = this.getNumberInd(currCellX,currCellY);
        this.numbersMenu.currNumberLength = 1.0*this.getNumberLength(
                                                            this.numbersMenu.currLineInd,
                                                            this.numbersMenu.currNumberInd
                                                    );
        
        let menu = this._field.numbersMenu.elem;


        left = e.pageX-menu.width()-this._field.step;
        if (left<0)
            left = e.pageX+this._field.step;
        menu.css('left',left+'px');

        top = e.pageY-menu.height()-this._field.step;
        if (top<0)
            top = e.pageY+this._field.step;
        menu.css('top',top+'px');

        menu.find('td').css('background-color','');
        menu.find('td[data-number='+this.numbersMenu.currNumberLength+']').css('background-color','#999');
        menu.show();

        this._field.horNums.redraw();
        this._field.vertNums.redraw();

        this._ctx.lineWidth = 3;
        this._ctx.strokeStyle = "rgb(255, 0, 0)";
        this._ctx.strokeRect(this._field.step*currCellX, this._field.step*currCellY, this._field.step, this._field.step);
    }
}