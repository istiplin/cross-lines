class HorizontalNumbers extends Numbers{
    constructor(field,numsList,className = null) {
        super(field,numsList,'horNums');
    }
    get canvasWidth(){
        return this.getPixelsCount(this.lineNumsMaxCount);
    }

    get canvasHeight(){
        return this.getPixelsCount(this.lineCount);
    }

    getLineInd(cellX,cellY){
        return cellY-1;
    }

    getNumberInd(cellX,cellY){
        let lineInd = this.getLineInd(cellX,cellY);

        if (this._numsList[lineInd]===undefined)
            return 0;

        return cellX - (this.lineNumsMaxCount - this._numsList[lineInd].length+1);
    }

    getNumCellX(x,y) {
        return (x+1)*this._field.step+1;
    }
	
	getNumCellY(x,y) {
		return (y+2)*this._field.step-3;
	}
	
	drawGrid(){
		for(let y = 0,k=0; y<=this.canvasHeight; y+=this._field.step, k++)
		{
			if (k%5==1)
				this._ctx.lineWidth = 2;
				
			this._ctx.beginPath();
			this._ctx.moveTo(0,y);
			this._ctx.lineTo(this.canvasWidth,y);
			this._ctx.stroke();
			
			this._ctx.lineWidth = 1;
		}
		for(let x = this.canvasWidth, k=0; x>0; x-=this._field.step, k++)
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