class Cells{
    constructor(field) {
            this._field = field;

            this._canvas = field.elem.getElementsByClassName('cells')[0];
            this._ctx = this._canvas.getContext("2d");

            this.resizeCanvas();
    }

    get width(){
            return this._field.vertNums.lineCount;
    }

    get height(){
            return this._field.horNums.lineCount
    }
	
    get canvasWidth(){
            return (this.width+2)*this._field.step;
    }

    get canvasHeight(){
            return (this.height+2)*this._field.step;
    }

    resizeCanvas(){
        this._canvas.width = this.canvasWidth;
        this._canvas.height = this.canvasHeight;
    }

    redraw(list){
        this._ctx.clearRect(0,0,this._canvas.width,this._canvas.height);
        this.resizeCanvas();
        this.draw(list);
    }
	
    drawGrid(){
        
        for(let y = this._field.step, k=0; y<=this.canvasHeight-this._field.step; y+=this._field.step,k++){
                if (k%5==0)
                        this._ctx.lineWidth = 2;

                this._ctx.beginPath();
                this._ctx.moveTo(this._field.step,y);
                this._ctx.lineTo(this.canvasWidth-this._field.step,y);
                this._ctx.stroke();

                this._ctx.lineWidth = 1;


                if (k!=this.height)
                {
                        let fontSize = this._field.step*12/16;
                        this._ctx.font = "bold "+fontSize+"px Arial";
                        this._ctx.fillText(k, 0, y+this._field.step-3);
                }

        }
        
        for(let x = this._field.step, k=0; x<=this.canvasWidth-this._field.step; x+=this._field.step, k++){
            if (k%5==0)
                this._ctx.lineWidth = 2;

            this._ctx.beginPath();
            this._ctx.moveTo(x,this._field.step);
            this._ctx.lineTo(x,this.canvasHeight-this._field.step);
            this._ctx.stroke();

            this._ctx.lineWidth = 1;


            if (k!=this.width){
                let fontSize = this._field.step*12/16;
                this._ctx.font = "bold "+fontSize+"px Arial";
                this._ctx.fillText(k, x, this._field.step-4);
            }
        }
    }
	
    draw(list){
        this.drawGrid();

        this.list = list;
        if (!list){
            return;
        }

        for(let y=0; y<this.height; y++)
        for(let x=0; x<this.width; x++)
        {
            if (this.list[y][x]=='1')
            {
                this._ctx.fillStyle = "rgba(0, 0, 0, 0.8)"; 
                this._ctx.fillRect((x+1)*this._field.step, (y+1)*this._field.step, this._field.step, this._field.step);
            }
            else if (this.list[y][x]=='2')
            {
                this._ctx.beginPath();
                this._ctx.moveTo((x+1)*this._field.step,(y+1)*this._field.step);
                this._ctx.lineTo((x+2)*this._field.step,(y+2)*this._field.step);
                this._ctx.stroke();

                this._ctx.beginPath();
                this._ctx.moveTo((x+2)*this._field.step,(y+1)*this._field.step);
                this._ctx.lineTo((x+1)*this._field.step,(y+2)*this._field.step);
                this._ctx.stroke();
            }
        }
    }
}