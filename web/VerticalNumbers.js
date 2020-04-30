class VerticalNumbers extends Numbers{
	constructor(field,numsList) {
		super(field,numsList);
		this._canvas = field.elem.getElementsByClassName('vertNums')[0];
		this._ctx = this._canvas.getContext("2d");
		this.resizeCanvas();
	}
	get canvasWidth(){
		return (this.lineCount+2)*this._field.step;
	}
	
	get canvasHeight(){
		return (this.lineNumsMaxCount+2)*this._field.step;
	}
	
	getLineInd(cellX,cellY){
		return cellX-1;
	}
	
	getNumberInd(cellX,cellY){
		let lineInd = this.getLineInd(cellX,cellY);
		
		if (this._numsList[lineInd]===undefined)
			return 0;
		
		return cellY - (this.lineNumsMaxCount - this._numsList[lineInd].length+1);
	}
	
	getNumCellX(x,y) {
		return (y+1)*this._field.step+1;
	}
	
	getNumCellY(x,y) {
		return (x+2)*this._field.step-3;
	}
	
	drawGrid(){
		for(let y = this.canvasHeight,k=0; y>0; y-=this._field.step, k++)
		{
			if (k%5==1)
				this._ctx.lineWidth = 2;
				
			this._ctx.beginPath();
			this._ctx.moveTo(0,y);
			this._ctx.lineTo(this.canvasWidth,y);
			this._ctx.stroke();
			
			this._ctx.lineWidth = 1;
		}
		for(let x = 0, k=0; x<=this.canvasWidth; x+=this._field.step, k++)
		{
			if (k%5==1)
				this._ctx.lineWidth = 2;
				
			this._ctx.beginPath();
			this._ctx.moveTo(x,0);
			this._ctx.lineTo(x,this.canvasHeight,);
			this._ctx.stroke();
			
			this._ctx.lineWidth = 1;
		}
	}
}