class Numbers{
	constructor(field,numsList) {
		this._field = field;
		
		if (numsList==null)
			this._numsList = [];
		else
			this._numsList = numsList;

		//this._canvas.onclick = this.canvasOnClick.bind(this);
	}
	
	get numsList(){
		return this._numsList;
	}
	
	get lineNumsMaxCount(){
		if (this._lineNumsMaxCount!=null)
			return this._lineNumsMaxCount;
			
		this._lineNumsMaxCount = 0;
		for (let i=0; i<this._numsList.length; i++)
			this._lineNumsMaxCount = Math.max(this._lineNumsMaxCount,this._numsList[i].length);
		return this._lineNumsMaxCount;
	}
	
	get lineCount(){
		return this._numsList.length;
	}
	
	getNumberLength(lineInd,numberInd) {
		if (this._numsList[lineInd]===undefined || this._numsList[lineInd][numberInd]===undefined)
			return undefined;
		
		return this._numsList[lineInd][numberInd];
	}
	
	changeNumsList(){	
		let numberLength = this._field.currNumberLength
		
		if (Number.isInteger(numberLength) && numberLength>0)
		{
			if (this._field.currLineInd<0)
				this._numsList.unshift([numberLength]);
			else if (this._field.currLineInd>this._numsList.length-1)
				this._numsList[this._field.currLineInd] = [numberLength];
			else if (this._field.currNumberInd<0)
				this._numsList[this._field.currLineInd].unshift(+numberLength);
			else
				this._numsList[this._field.currLineInd][this._field.currNumberInd] = +numberLength;
		}
		else
		{
			if (this._field.currLineInd>=0 && this._field.currLineInd<this._numsList.length)
			{
				if (this._numsList[this._field.currLineInd][this._field.currNumberInd]!==undefined)
					this._numsList[this._field.currLineInd].splice(this._field.currNumberInd,1);
				if (this._numsList[this._field.currLineInd].length==0)
					this._numsList.splice(this._field.currLineInd,1);
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
			let x1 = (this.lineNumsMaxCount - this._numsList[y].length) + x;
			if (this._numsList[y][x]!=undefined)
			{
				let fontSize = this._field.step*12/16;
				this._ctx.font = "bold "+fontSize+"px Arial";
				this._ctx.fillText(this._numsList[y][x], this.getNumCellX(x1,y), this.getNumCellY(x1,y));
			}
		}
	}
	
	get sum(){
		let sum = 0;
		for(let y=0; y<this.lineCount; y++)
		for(let x=0; x<this._numsList[y].length; x++)
		{
			sum+=this._numsList[y][x];
		}
		
		return sum;
	}
}