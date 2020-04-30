class Field{
	constructor(id,horNumsList,vertNumsList,cellsList) {
		this.elem = document.getElementById(id);
		this._step = 12;

		this.elem.innerHTML = this.elemInnerHtml;
		
		this.horNums = new HorizontalNumbers(this,horNumsList);
		this.vertNums = new VerticalNumbers(this,vertNumsList);
		this.cells = new Cells(this);
		
		this.currNumsName = '';
		this.elem.onclick = this.onClick.bind(this);
	}
	
	get numbersMenuHtml(){
		let html="";
		html+="<table class='numbers-menu'>";
		for(var y=0; y<10; y++)
		{
			html+="<tr>";
			for(var x=0; x<10; x++)
			{
				let num = y*10+x;
				html+="<td class='number' data-number="+num+">";
				html+=num;
				html+="</td>";
			}
			html+="</tr>";
		}
		html+="</table>";
		
		return html;
	}
	
	get numbersMenuElem(){
		if (this._numbersMenuElem!=null)
			return this._numbersMenuElem;
		
		let elem = $(this.elem).find('.numbers-menu');
		elem.hide();
		$(elem).find('.number').click(this.numberMenuOnClick.bind(this));
		return this._numbersMenuElem = elem;
	}
	
	numberMenuOnClick(e){
		if (this.currNumberLength == $(e.target).data('number')){
			this.numbersMenuElem.hide();
			return;
		}
			
		this.currNumberLength = $(e.target).data('number');
		this[this.currNumsName].changeNumsList();
		this[this.currNumsName].redraw();
		this.cells.redraw();
		this.numbersMenuElem.hide();
	}
	
	get elemInnerHtml(){
		let html="";
		html+= "<table>";
		html+=  "<tr>";
		html+=   "<td></td>";
		html+=   "<td><canvas width=0 height=0 class='vertNums'></canvas></td>";
		html+=  "</tr>";
		html+=  "<tr>";
		html+=   "<td><canvas width=0 height=0 class='horNums'></canvas></td>";
		html+=   "<td><canvas width=0 height=0 class='cells'></canvas></td>";
		html+=  "</tr>";
		html+= "</table>";
		html+= this.numbersMenuHtml;
		return html;
	}
	
	get horNumsList(){
		return this.horNums._numsList
	}
	
	get vertNumsList(){
		return this.vertNums._numsList
	}
	
	get cellsList(){
		return this.cells.list;
	}
	
	get step(){
		return this._step;
	}
	
	redraw(list){
		this.horNums.redraw();
		this.vertNums.redraw();
		this.cells.redraw(list);
	}
	
	draw(list){
		this.horNums.draw();
		this.vertNums.draw();
		this.cells.draw(list);
	}
	
	onClick(e){
		this.numbersMenuElem.hide();
		
		this.currNumsName = e.target.className;
		this.currLineInd = null;
		this.currNumberInd = null;
		this.currNumberLength = null;

		this.horNums.redraw();
		this.vertNums.redraw();
		
		if (e.target.className!='horNums' && e.target.className!='vertNums')
			return;
			
		this.canvasOnClick.call(this[e.target.className],e);
		
	}

	canvasOnClick(e) {
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
		
		this._field.currCellX = Math.floor(x/this._field.step);
		this._field.currCellY = Math.floor(y/this._field.step);
		this._field.currLineInd = this.getLineInd(this._field.currCellX,this._field.currCellY);
		this._field.currNumberInd = this.getNumberInd(this._field.currCellX,this._field.currCellY);
		this._field.currNumberLength = 1.0*this.getNumberLength(this._field.currLineInd,this._field.currNumberInd);
		
		let menu = this._field.numbersMenuElem;
		

			
		

		left = e.pageX-menu.width()-this._field.step;
		if (left<0)
			left = e.pageX+this._field.step;
		menu.css('left',left+'px');
		
		top = e.pageY-menu.height()-this._field.step;
		if (top<0)
			top = e.pageY+this._field.step;
		menu.css('top',top+'px');
		
		menu.find('td').css('background-color','');
		menu.find('td[data-number='+this._field.currNumberLength+']').css('background-color','#999');
		menu.show();
		
		this._field.horNums.redraw();
		this._field.vertNums.redraw();
		
		this._ctx.lineWidth = 3;
		this._ctx.strokeStyle = "rgb(255, 0, 0)";
		this._ctx.strokeRect(this._field.step*this._field.currCellX, this._field.step*this._field.currCellY, this._field.step, this._field.step);
	}
	
	get horNumsSum(){
		return this.horNums.sum;
	}
	
	get vertNumsSum(){
		return this.vertNums.sum;
	}
}